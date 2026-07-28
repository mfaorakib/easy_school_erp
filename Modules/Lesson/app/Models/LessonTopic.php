<?php

namespace Modules\Lesson\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonTopic extends Model
{
    protected $fillable = ['lesson_id', 'title', 'is_complete', 'completed_on', 'position'];

    protected $casts = ['is_complete' => 'boolean', 'completed_on' => 'date'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
