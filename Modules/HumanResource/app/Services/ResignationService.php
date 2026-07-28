<?php

namespace Modules\HumanResource\Services;

use Illuminate\Support\Facades\Auth;
use Modules\HumanResource\Models\ResignationApplication;

/**
 * Mirrors Leave's apply→review shape exactly. The one behavior Leave doesn't
 * have: approving a resignation stamps the staff record itself
 * (leaving_date + is_active=false) — the same fields the Documents module's
 * Experience Certificate already reads, so approving a resignation makes
 * that staff member immediately eligible for one.
 */
class ResignationService
{
    public function apply(int $staffId, string $intendedLastDay, ?string $reason = null): ResignationApplication
    {
        return ResignationApplication::create([
            'staff_id'          => $staffId,
            'reason'            => $reason,
            'intended_last_day' => $intendedLastDay,
            'status'            => ResignationApplication::STATUS_PENDING,
        ]);
    }

    public function review(ResignationApplication $application, string $status, ?string $note = null): ResignationApplication
    {
        $application->update([
            'status'      => $status,
            'review_note' => $note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($status === ResignationApplication::STATUS_APPROVED) {
            $application->staff->update([
                'leaving_date' => $application->intended_last_day,
                'is_active'    => false,
            ]);
        }

        return $application;
    }
}
