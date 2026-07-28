<?php

namespace Modules\FrontOffice\Services;

use Illuminate\Support\Facades\Auth;
use Modules\FrontOffice\Models\AdmissionEnquiry;
use Modules\FrontOffice\Models\EnquiryFollowup;
use Modules\FrontOffice\Models\Visitor;

/**
 * Front-desk helpers. Most front-office data is plain CRUD; the only real logic
 * is threading enquiry follow-ups (a follow-up also advances the enquiry's
 * next-follow-up date) and checking a visitor out.
 */
class FrontOfficeService
{
    /** Record a follow-up and roll the enquiry's next follow-up date forward. */
    public function addFollowup(AdmissionEnquiry $enquiry, array $data): EnquiryFollowup
    {
        $followup = $enquiry->followups()->create([
            'follow_up_date'      => $data['follow_up_date'] ?? now()->toDateString(),
            'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
            'response'            => $data['response'] ?? null,
            'note'                => $data['note'] ?? null,
            'created_by'          => Auth::id(),
        ]);

        $enquiry->update(['next_follow_up_date' => $followup->next_follow_up_date]);

        return $followup;
    }

    /** Stamp a visitor's exit time (defaults to now). */
    public function checkOut(Visitor $visitor, ?string $time = null): Visitor
    {
        $visitor->update(['out_time' => $time ?? now()->format('H:i')]);

        return $visitor;
    }
}
