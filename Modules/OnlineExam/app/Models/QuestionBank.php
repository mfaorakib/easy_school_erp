<?php

namespace Modules\OnlineExam\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;

/**
 * A reusable question. type ∈ mcq|truefalse|fill.
 *  - mcq: correct answers are the question_options where is_correct = true
 *  - truefalse: correct answer is correct_bool
 *  - fill: reference answer in answer_text; graded manually
 * Year-scoped.
 */
class QuestionBank extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const TYPE_MCQ = 'mcq';
    public const TYPE_TRUEFALSE = 'truefalse';
    public const TYPE_FILL = 'fill';

    protected $fillable = [
        'question_group_id', 'class_id', 'section_id', 'type', 'difficulty',
        'question', 'marks', 'correct_bool', 'answer_text', 'is_active',
        'academic_year_id', 'created_by',
    ];

    protected $casts = [
        'marks'        => 'decimal:2',
        'correct_bool' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class, 'question_group_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function isMcq(): bool
    {
        return $this->type === self::TYPE_MCQ;
    }

    public function isTrueFalse(): bool
    {
        return $this->type === self::TYPE_TRUEFALSE;
    }

    public function isFill(): bool
    {
        return $this->type === self::TYPE_FILL;
    }

    /** Objective questions are auto-gradable; fill-in-the-blank needs manual marking. */
    public function isAutoGradable(): bool
    {
        return $this->isMcq() || $this->isTrueFalse();
    }

    /** IDs of the options flagged correct (mcq only). */
    public function correctOptionIds(): array
    {
        return $this->options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
