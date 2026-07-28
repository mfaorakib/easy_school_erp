<?php

namespace Modules\Library\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Services\AccountingService;
use Modules\Foundation\Services\SettingService;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookIssue;
use Modules\Library\Models\LibraryMember;
use RuntimeException;

/**
 * Book issue / return.
 *  - issue: requires an available copy; sets due = issue + loan_days; decrements stock
 *  - return: fine = days_overdue × fine_per_day (0 if on time); restores stock
 * Loan period and fine rate come from settings (with sane defaults).
 */
class LibraryService
{
    public function __construct(private readonly SettingService $settings, private readonly AccountingService $accounting) {}

    public function loanDays(): int
    {
        return (int) $this->settings->get('library_loan_days', 14);
    }

    public function finePerDay(): float
    {
        return (float) $this->settings->get('library_fine_per_day', 5);
    }

    public function issue(Book $book, LibraryMember $member, ?string $dueDate = null): BookIssue
    {
        if ($book->available < 1) {
            throw new RuntimeException('No copies available for this book.');
        }

        // Reference rule: a member cannot hold the same title twice at once.
        $alreadyHolding = BookIssue::where('book_id', $book->id)
            ->where('library_member_id', $member->id)
            ->where('is_returned', false)->exists();

        if ($alreadyHolding) {
            throw new RuntimeException('This member already has this book issued.');
        }

        return DB::transaction(function () use ($book, $member, $dueDate) {
            $issue = BookIssue::create([
                'book_id'           => $book->id,
                'library_member_id' => $member->id,
                'issue_date'        => now()->toDateString(),
                'due_date'          => $dueDate ?: now()->addDays($this->loanDays())->toDateString(),
                'is_returned'       => false,
                'issued_by'         => Auth::id(),
            ]);

            $book->decrement('available');

            return $issue;
        });
    }

    /** Fine that would apply if returned on $date (0 if not overdue). */
    public function fineFor(BookIssue $issue, ?Carbon $date = null): float
    {
        $date ??= now();
        if ($date->lessThanOrEqualTo($issue->due_date)) {
            return 0.0;
        }

        return round(floor($issue->due_date->diffInDays($date)) * $this->finePerDay(), 2);
    }

    public function returnBook(BookIssue $issue, ?string $returnDate = null): BookIssue
    {
        if ($issue->is_returned) {
            return $issue;
        }

        return DB::transaction(function () use ($issue, $returnDate) {
            $on = $returnDate ? Carbon::parse($returnDate) : now();

            $issue->update([
                'return_date' => $on->toDateString(),
                'fine'        => $this->fineFor($issue, $on),
                'is_returned' => true,
            ]);

            $issue->book?->increment('available');

            return $issue;
        });
    }

    /** Collect the fine on a returned issue and post it into Accounting as income. */
    public function collectFine(BookIssue $issue): ?AccountTransaction
    {
        if (! $issue->is_returned) {
            throw new RuntimeException('This book has not been returned yet.');
        }

        if ((float) $issue->fine <= 0) {
            throw new RuntimeException('There is no fine on this issue.');
        }

        if ($issue->fine_collected) {
            throw new RuntimeException('This fine has already been collected.');
        }

        return DB::transaction(function () use ($issue) {
            // Member may have been soft-deleted after the fine accrued; load it including
            // trashed so its real label still renders, and fall back if it's gone entirely.
            $member = $issue->member()->withTrashed()->first();

            $transaction = $this->accounting->postFromSource(
                'library_fine',
                $issue->id,
                'income',
                'Library Fines',
                (float) $issue->fine,
                $issue->return_date->toDateString(),
                'Library fine — '.optional($issue->book)->title.' ('.($member?->displayLabel() ?? 'Unknown member').')',
            );

            $issue->update(['fine_collected' => true, 'fine_collected_at' => now()]);

            return $transaction;
        });
    }

    /** Members list filtered by type / student class-section / staff name search. */
    public function filterMembers(?string $type, ?int $classId, ?int $sectionId, ?string $staffSearch): \Illuminate\Support\Collection
    {
        $query = LibraryMember::with(['user'])
            ->where('is_active', true)
            ->with([
                'student.records' => fn ($q) => $q->live()->with(['schoolClass', 'section']),
                'staff.designation',
                'staff.department',
            ]);

        if ($type === 'student') {
            $query->students();
        } elseif ($type === 'staff') {
            $query->staff();
        }

        if ($classId) {
            $query->whereHas('student.records', fn ($q) => $q->live()
                ->where('class_id', $classId)
                ->when($sectionId, fn ($q2) => $q2->where('section_id', $sectionId)));
        }

        if (! empty($staffSearch)) {
            $query->where(function ($q) use ($staffSearch) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$staffSearch}%"))
                    ->orWhereHas('staff', fn ($s) => $s->where('full_name', 'like', "%{$staffSearch}%"))
                    ->orWhereHas('staff.designation', fn ($d) => $d->where('title', 'like', "%{$staffSearch}%"))
                    ->orWhereHas('staff.department', fn ($d) => $d->where('name', 'like', "%{$staffSearch}%"));
            });
        }

        return $query->get();
    }

    /** Book issue history filtered by member type/class/section/staff search, book, status, and date range. */
    public function issueHistory(array $filters): \Illuminate\Support\Collection
    {
        $query = BookIssue::with([
            'book',
            'issuedBy',
            'member.user',
            'member.student.records' => fn ($q) => $q->live()->with(['schoolClass', 'section']),
            'member.staff.designation',
            'member.staff.department',
        ])->latest('issue_date');

        if (! empty($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'open') {
                $query->where('is_returned', false);
            } elseif ($filters['status'] === 'returned') {
                $query->where('is_returned', true);
            }
        }

        if (! empty($filters['from'])) {
            $query->whereDate('issue_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('issue_date', '<=', $filters['to']);
        }

        $memberType = $filters['member_type'] ?? null;
        $classId = $filters['class_id'] ?? null;
        $sectionId = $filters['section_id'] ?? null;
        $staffSearch = $filters['staff_search'] ?? null;

        if ($memberType || $classId || ! empty($staffSearch)) {
            $query->whereHas('member', function ($q) use ($memberType, $classId, $sectionId, $staffSearch) {
                if ($memberType === 'student') {
                    $q->students();
                } elseif ($memberType === 'staff') {
                    $q->staff();
                }

                if ($classId) {
                    $q->whereHas('student.records', fn ($q2) => $q2->live()
                        ->where('class_id', $classId)
                        ->when($sectionId, fn ($q3) => $q3->where('section_id', $sectionId)));
                }

                if (! empty($staffSearch)) {
                    $q->where(function ($q2) use ($staffSearch) {
                        $q2->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$staffSearch}%"))
                            ->orWhereHas('staff', fn ($s) => $s->where('full_name', 'like', "%{$staffSearch}%"))
                            ->orWhereHas('staff.designation', fn ($d) => $d->where('title', 'like', "%{$staffSearch}%"))
                            ->orWhereHas('staff.department', fn ($d) => $d->where('name', 'like', "%{$staffSearch}%"));
                    });
                }
            });
        }

        return $query->get();
    }
}
