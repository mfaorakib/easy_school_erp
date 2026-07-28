<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\AcademicCore\Models\Guardian;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\BankAccount;
use Modules\Accounting\Services\AccountingService;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Communication\Models\Event;
use Modules\Communication\Models\Notice;
use Modules\Fees\Models\FeePayment;
use Modules\HumanResource\Models\Staff;

/**
 * Aggregates live figures for the admin dashboard from across the modules.
 * Every read degrades gracefully — an empty module just yields zeros.
 */
class DashboardService
{
    public function __construct(private AccountingService $accounting) {}

    /** @return array{students:int,teachers:int,staff:int,guardians:int,classes:int} */
    public function kpis(): array
    {
        return [
            'students'  => (int) StudentRecord::live()->distinct('student_id')->count('student_id'),
            'teachers'  => (int) Staff::teachers()->count(),
            'staff'     => (int) Staff::where('is_active', true)->count(),
            'guardians' => (int) Guardian::count(),
            'classes'   => (int) SchoolClass::count(),
        ];
    }

    /**
     * @return array{series:array<int,array{label:string,income:float,expense:float}>,
     *   income_month:float, expense_month:float, fees_month:float, bank_balance:float}
     */
    public function finance(): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $series[] = [
                'label'   => $month->format('M'),
                'income'  => $this->sum('income', $month),
                'expense' => $this->sum('expense', $month),
            ];
        }

        $thisMonth = Carbon::now();
        $feesMonth = (float) FeePayment::whereYear('paid_on', $thisMonth->year)
            ->whereMonth('paid_on', $thisMonth->month)->sum('amount');

        $bank = (float) BankAccount::all()->sum(fn ($a) => $this->accounting->bankBalance($a));

        return [
            'series'        => $series,
            'income_month'  => $this->sum('income', $thisMonth),
            'expense_month' => $this->sum('expense', $thisMonth),
            'fees_month'    => round($feesMonth, 2),
            'bank_balance'  => round($bank, 2),
        ];
    }

    private function sum(string $type, Carbon $month): float
    {
        return (float) AccountTransaction::where('type', $type)
            ->whereYear('transaction_date', $month->year)
            ->whereMonth('transaction_date', $month->month)
            ->sum('amount');
    }

    /** @return array{present:int,late:int,absent:int,half:int,total:int,rate:float} */
    public function attendanceToday(): array
    {
        $rows = StudentAttendance::whereDate('date', Carbon::today())->get();
        $count = fn (string $c) => $rows->filter(fn ($r) => $r->status?->value === $c)->count();

        $p = $count('P'); $l = $count('L'); $a = $count('A'); $f = $count('F');
        $total = $p + $l + $a + $f;
        $rate = $total > 0 ? round(($p + $l + $f * 0.5) / $total * 100, 1) : 0.0;

        return ['present' => $p, 'late' => $l, 'absent' => $a, 'half' => $f, 'total' => $total, 'rate' => $rate];
    }

    /** @return array<int,array{label:string,total:int}> live student count per class, in class position order. */
    public function studentsByClass(): array
    {
        return StudentRecord::live()
            ->selectRaw('class_id, COUNT(DISTINCT student_id) as total')
            ->groupBy('class_id')
            ->with('schoolClass:id,name,position')
            ->get()
            ->sortBy(fn ($row) => optional($row->schoolClass)->position ?? 0)
            ->map(fn ($row) => ['label' => optional($row->schoolClass)->name ?? '—', 'total' => (int) $row->total])
            ->values()
            ->all();
    }

    /** @return array<int,array{label:string,rate:float}> today's attendance rate, oldest first. */
    public function attendanceTrend(int $days = 7): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $byDay = StudentAttendance::where('date', '>=', $start)->get()->groupBy(fn ($r) => $r->date->toDateString());

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $rows = $byDay->get($day->toDateString(), collect());
            $count = fn (string $c) => $rows->filter(fn ($r) => $r->status?->value === $c)->count();

            $p = $count('P'); $l = $count('L'); $a = $count('A'); $f = $count('F');
            $total = $p + $l + $a + $f;
            $rate = $total > 0 ? round(($p + $l + $f * 0.5) / $total * 100, 1) : 0.0;

            $out[] = ['label' => $day->format('D'), 'rate' => $rate];
        }

        return $out;
    }

    public function notices(int $limit = 5): Collection
    {
        return Notice::where('is_published', true)->latest('notice_date')->latest('id')->take($limit)->get();
    }

    public function upcomingEvents(int $limit = 5): Collection
    {
        return Event::where('start_date', '>=', Carbon::today())->orderBy('start_date')->take($limit)->get();
    }

    /**
     * A merged recent-activity feed from a few modules.
     * @return array<int,array{icon:string,text:string,time:?\Illuminate\Support\Carbon,url?:string}>
     */
    public function recentActivity(int $limit = 6): array
    {
        $items = collect();

        foreach (\Modules\FrontOffice\Models\AdmissionEnquiry::latest('id')->take(4)->get() as $e) {
            $items->push(['icon' => 'fa-solid fa-pen-to-square', 'text' => 'Admission enquiry — '.$e->name, 'time' => $e->created_at]);
        }
        foreach (FeePayment::with('assignment.student')->latest('id')->take(4)->get() as $p) {
            $student = optional($p->assignment)->student;
            $name = optional($student)->full_name ?? 'a student';
            $item = ['icon' => 'fa-solid fa-sack-dollar', 'text' => 'Fee payment '.number_format((float) $p->amount, 0).' — '.$name, 'time' => $p->created_at];
            if ($student) {
                $item['url'] = route('academic.students.show', $student->id);
            }
            $items->push($item);
        }
        foreach (\Modules\FrontOffice\Models\Complaint::latest('id')->take(3)->get() as $c) {
            $items->push(['icon' => 'fa-solid fa-triangle-exclamation', 'text' => 'Complaint — '.$c->complainant_name, 'time' => $c->created_at]);
        }

        return $items->sortByDesc('time')->take($limit)->values()->all();
    }
}
