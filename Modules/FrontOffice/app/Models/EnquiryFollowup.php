<?php

namespace Modules\FrontOffice\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A follow-up on an admission enquiry. */
class EnquiryFollowup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admission_enquiry_id', 'follow_up_date', 'next_follow_up_date',
        'response', 'note', 'created_by',
    ];

    protected $casts = [
        'follow_up_date'      => 'date',
        'next_follow_up_date' => 'date',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(AdmissionEnquiry::class, 'admission_enquiry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
