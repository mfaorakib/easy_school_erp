<?php

namespace Modules\Admission\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Student;
use Modules\Foundation\Models\BaseSetup;

/** A public admission application, pending staff review. */
class AdmissionApplication extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reference_no', 'first_name', 'last_name', 'gender_id', 'date_of_birth', 'desired_class_id',
        'guardian_name', 'guardian_relation', 'guardian_mobile', 'guardian_email',
        'present_address', 'previous_school', 'photo_path',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
        'student_id', 'unique_id', 'academic_year_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'reviewed_at'   => 'datetime',
    ];

    public function desiredClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'desired_class_id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(BaseSetup::class, 'gender_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmissionApplicationDocument::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($q)
    {
        return $q->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeRejected($q)
    {
        return $q->where('status', self::STATUS_REJECTED);
    }
}
