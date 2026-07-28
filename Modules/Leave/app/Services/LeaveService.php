<?php

namespace Modules\Leave\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Leave\Models\LeaveApplication;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Models\Shift;
use Modules\Leave\Models\StaffShift;
use Modules\Leave\Models\TeacherEvaluation;

/**
 * Leave, evaluation and shift logic.
 *
 * Leave balance = the type's annual quota − the days already taken on APPROVED
 * applications of that type this year. A pending application never consumes
 * balance until it is approved.
 */
class LeaveService
{
    /** Inclusive day count between two dates. */
    public function dayCount(string $from, string $to): int
    {
        return Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
    }

    /** Create a leave application (pending). */
    public function apply(array $data): LeaveApplication
    {
        return LeaveApplication::create([
            'staff_id'      => $data['staff_id'],
            'leave_type_id' => $data['leave_type_id'],
            'from_date'     => $data['from_date'],
            'to_date'       => $data['to_date'],
            'days'          => $this->dayCount($data['from_date'], $data['to_date']),
            'reason'        => $data['reason'] ?? null,
            'status'        => LeaveApplication::STATUS_PENDING,
        ]);
    }

    /** Approve or reject an application. */
    public function review(LeaveApplication $application, string $status, ?string $note = null): LeaveApplication
    {
        $application->update([
            'status'      => $status,
            'review_note' => $note,
            'reviewed_by' => Auth::id(),
        ]);

        return $application;
    }

    /** Days remaining for a staff member on a leave type this year. */
    public function balance(int $staffId, LeaveType $type): int
    {
        $used = (int) LeaveApplication::where('staff_id', $staffId)
            ->where('leave_type_id', $type->id)
            ->where('status', LeaveApplication::STATUS_APPROVED)
            ->sum('days');

        return max(0, (int) $type->days_allowed - $used);
    }

    /** @return array<int,array{type:LeaveType, allowed:int, used:int, remaining:int}> */
    public function balanceSheet(int $staffId): array
    {
        return LeaveType::active()->orderBy('name')->get()->map(function ($type) use ($staffId) {
            $used = (int) LeaveApplication::where('staff_id', $staffId)
                ->where('leave_type_id', $type->id)
                ->where('status', LeaveApplication::STATUS_APPROVED)->sum('days');

            return ['type' => $type, 'allowed' => (int) $type->days_allowed, 'used' => $used, 'remaining' => max(0, (int) $type->days_allowed - $used)];
        })->all();
    }

    // -------------------------------------------------------------- Shifts

    public function assignShift(int $staffId, int $shiftId): StaffShift
    {
        return StaffShift::updateOrCreate(['staff_id' => $staffId], ['shift_id' => $shiftId]);
    }

    // ---------------------------------------------------------- Evaluation

    /**
     * Record a teacher evaluation. `criteria` is a list of ['name'=>, 'score'=>];
     * total_score is the average of the scores.
     */
    public function evaluate(array $data): TeacherEvaluation
    {
        $criteria = array_values(array_filter($data['criteria'] ?? [], fn ($c) => ! empty($c['name'])));
        $scores = array_map(fn ($c) => (float) ($c['score'] ?? 0), $criteria);
        $total = count($scores) ? round(array_sum($scores) / count($scores), 2) : 0.0;

        return TeacherEvaluation::create([
            'staff_id'        => $data['staff_id'],
            'term'            => $data['term'] ?? null,
            'evaluation_date' => $data['evaluation_date'] ?? now()->toDateString(),
            'criteria'        => $criteria,
            'total_score'     => $total,
            'remarks'         => $data['remarks'] ?? null,
            'evaluated_by'    => Auth::id(),
        ]);
    }
}
