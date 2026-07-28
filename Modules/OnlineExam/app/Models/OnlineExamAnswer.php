<?php

namespace Modules\OnlineExam\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A student's answer to one question in an attempt. Child of an attempt. */
class OnlineExamAnswer extends Model
{
    protected $fillable = [
        'attempt_id', 'question_bank_id', 'selected_options',
        'bool_answer', 'text_answer', 'obtain_marks', 'is_correct', 'marked_by',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'bool_answer'      => 'boolean',
        'obtain_marks'     => 'decimal:2',
        'is_correct'       => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(OnlineExamAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }
}
