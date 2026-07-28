<?php

namespace Modules\OnlineExam\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An MCQ answer option. Child of a question bank item (not year-scoped directly). */
class QuestionOption extends Model
{
    protected $fillable = ['question_bank_id', 'title', 'is_correct'];

    protected $casts = ['is_correct' => 'boolean'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }
}
