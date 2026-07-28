<?php

namespace Modules\Leave\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

/** A student leave/absence request. Year-scoped. */
class StudentLeaveApplication extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'student_id', 'leave_type_id', 'from_date', 'to_date', 'days', 'reason',
        'status', 'reviewed_by', 'review_note', 'academic_year_id',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function scopeStatus($q, string $status)
    {
        return $q->where('status', $status);
    }
}
