<?php

namespace Modules\Library\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookIssue;
use Modules\Library\Models\LibraryMember;
use Modules\Library\Services\LibraryService;

class IssueController extends Controller
{
    public function __construct(private readonly LibraryService $service) {}

    public function index(Request $request)
    {
        $memberType = $request->query('member_type');
        $classId = $request->query('class_id') ? (int) $request->query('class_id') : null;
        $sectionId = $request->query('section_id') ? (int) $request->query('section_id') : null;
        $staffSearch = $request->query('staff_search') ? trim($request->query('staff_search')) : null;

        $open = BookIssue::with(['book', 'member.user'])
            ->where('is_returned', false)->latest()->get()
            ->map(fn ($i) => ['issue' => $i, 'fine' => $this->service->fineFor($i)]);

        $members = $this->service->filterMembers($memberType, $classId, $sectionId, $staffSearch);

        return view('library::issue.index', [
            'open'        => $open,
            'books'       => Book::available()->orderBy('title')->get(),
            'members'     => $members,
            'dueHint'     => now()->addDays($this->service->loanDays())->toDateString(),
            'classes'     => \Modules\AcademicCore\Models\SchoolClass::active()->orderBy('name')->get(),
            'sections'    => \Modules\AcademicCore\Models\Section::active()->orderBy('name')->get(),
            'memberType'  => $memberType,
            'classId'     => $classId,
            'sectionId'   => $sectionId,
            'staffSearch' => $staffSearch,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id'           => ['required', 'integer', 'exists:books,id'],
            'library_member_id' => ['required', 'integer', 'exists:library_members,id'],
            'due_date'          => ['nullable', 'date'],
        ]);

        try {
            $this->service->issue(
                Book::findOrFail($data['book_id']),
                LibraryMember::findOrFail($data['library_member_id']),
                $data['due_date'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('library.issue.index', $request->only(['member_type', 'class_id', 'section_id', 'staff_search']))
            ->with('status', 'Book issued.');
    }

    public function returnBook(BookIssue $issue)
    {
        $this->service->returnBook($issue);

        return redirect()->route('library.issue.index')
            ->with('status', 'Book returned'.($issue->fresh()->fine > 0 ? " (fine {$issue->fresh()->fine})." : '.'));
    }

    public function collectFine(BookIssue $issue)
    {
        try {
            $this->service->collectFine($issue);
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('library.issue.history')->with('status', 'Fine collected.');
    }

    public function fineReceipt(BookIssue $issue)
    {
        abort_unless($issue->fine_collected, 404);

        return view('library::issue.fine_receipt', ['issue' => $issue->load(['book', 'member.user', 'member.student.records.schoolClass', 'member.student.records.section', 'member.staff.designation', 'member.staff.department'])]);
    }

    public function history(Request $request)
    {
        $memberType = $request->query('member_type');
        $classId = $request->query('class_id') ? (int) $request->query('class_id') : null;
        $sectionId = $request->query('section_id') ? (int) $request->query('section_id') : null;
        $staffSearch = $request->query('staff_search') ? trim($request->query('staff_search')) : null;
        $bookId = $request->query('book_id') ? (int) $request->query('book_id') : null;
        $status = $request->query('status') ? trim($request->query('status')) : null;
        $from = $request->query('from') ? trim($request->query('from')) : null;
        $to = $request->query('to') ? trim($request->query('to')) : null;

        $filters = [
            'member_type'  => $memberType,
            'class_id'     => $classId,
            'section_id'   => $sectionId,
            'staff_search' => $staffSearch,
            'book_id'      => $bookId,
            'status'       => $status,
            'from'         => $from,
            'to'           => $to,
        ];

        $issues = $this->service->issueHistory($filters);

        return view('library::issue.history', [
            'issues'      => $issues,
            'books'       => Book::orderBy('title')->get(),
            'classes'     => \Modules\AcademicCore\Models\SchoolClass::active()->orderBy('name')->get(),
            'sections'    => \Modules\AcademicCore\Models\Section::active()->orderBy('name')->get(),
            'memberType'  => $memberType,
            'classId'     => $classId,
            'sectionId'   => $sectionId,
            'staffSearch' => $staffSearch,
            'bookId'      => $bookId,
            'status'      => $status,
            'from'        => $from,
            'to'          => $to,
        ]);
    }
}
