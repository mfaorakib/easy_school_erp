<?php

namespace Modules\OnlineExam\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

/** One student's sitting of an online exam. Year-scoped. */
class OnlineExamAttempt extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_MARKED = 'marked';

    protected $fillable = [
        'online_exam_id', 'student_id', 'status', 'total_marks',
        'submitted_at', 'academic_year_id',
    ];

    protected $casts = [
        'total_marks'  => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(OnlineExamAnswer::class, 'attempt_id');
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_MARKED], true);
    }

    /** True while any answer still awaits marking (obtain_marks is null). */
    public function awaitsMarking(): bool
    {
        return $this->answers()->whereNull('obtain_marks')->exists();
    }
}
