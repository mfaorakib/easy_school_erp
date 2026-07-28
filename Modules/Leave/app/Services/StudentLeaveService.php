<?php

namespace Modules\Leave\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Leave\Models\StudentLeaveApplication;

/** Student leave/absence requests — parallel to LeaveService but for students, not staff. */
class StudentLeaveService
{
    /** Inclusive day count between two dates. */
    public function dayCount(string $from, string $to): int
    {
        return Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
    }

    /** Create a student leave application (pending). */
    public function apply(array $data): StudentLeaveApplication
    {
        return StudentLeaveApplication::create([
            'student_id'    => $data['student_id'],
            'leave_type_id' => $data['leave_type_id'],
            'from_date'     => $data['from_date'],
            'to_date'       => $data['to_date'],
            'days'          => $this->dayCount($data['from_date'], $data['to_date']),
            'reason'        => $data['reason'] ?? null,
            'status'        => StudentLeaveApplication::STATUS_PENDING,
        ]);
    }

    /** Approve or reject an application. */
    public function review(StudentLeaveApplication $application, string $status, ?int $reviewerId, ?string $note = null): StudentLeaveApplication
    {
        $application->update([
            'status'      => $status,
            'review_note' => $note,
            'reviewed_by' => $reviewerId,
        ]);

        return $application;
    }

    /** This student's own requests, newest first. */
    public function forStudent(int $studentId): Collection
    {
        return StudentLeaveApplication::where('student_id', $studentId)
            ->with('type')
            ->latest('id')
            ->get();
    }

    /** All requests awaiting admin action, across every student. */
    public function pending(): Collection
    {
        return StudentLeaveApplication::status(StudentLeaveApplication::STATUS_PENDING)
            ->with(['student', 'type'])
            ->latest('id')
            ->get();
    }

    /** Every request (any status), for the admin history view. */
    public function all(): Collection
    {
        return StudentLeaveApplication::with(['student', 'type'])->latest('id')->get();
    }
}
