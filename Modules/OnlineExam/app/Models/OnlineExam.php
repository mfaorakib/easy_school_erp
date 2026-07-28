<?php

namespace Modules\OnlineExam\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;

/**
 * An online exam. Questions are attached from the question bank via the
 * online_exam_questions pivot. Total mark is derived from the attached questions.
 * Year-scoped.
 */
class OnlineExam extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $table = 'online_exams';

    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'title', 'exam_date',
        'start_time', 'end_time', 'duration_minutes', 'instruction',
        'auto_mark', 'is_published', 'academic_year_id', 'created_by',
    ];

    protected $casts = [
        'exam_date'    => 'date',
        'auto_mark'    => 'boolean',
        'is_published' => 'boolean',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** Attached question-bank items, ordered by their assigned position. */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBank::class, 'online_exam_questions', 'online_exam_id', 'question_bank_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('online_exam_questions.position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    /** Sum of the marks of the attached questions. */
    public function totalMarks(): float
    {
        return (float) $this->questions->sum('marks');
    }
}
