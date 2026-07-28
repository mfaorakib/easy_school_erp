<?php

namespace Modules\FrontOffice\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\HumanResource\Models\Staff;

/** An admission enquiry / lead. Year-scoped. */
class AdmissionEnquiry extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'description', 'class_id', 'source',
        'no_of_child', 'enquiry_date', 'next_follow_up_date', 'assigned_to',
        'status', 'note', 'academic_year_id',
    ];

    protected $casts = [
        'enquiry_date'        => 'date',
        'next_follow_up_date' => 'date',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(EnquiryFollowup::class)->latest('follow_up_date')->latest('id');
    }
}
