<?php

namespace Modules\Lesson\Services;

use Modules\Lesson\Models\Lesson;
use Modules\Lesson\Models\LessonTopic;

/**
 * Lesson topic helpers. Completing a topic stamps the completion date; the
 * lesson's progress % is derived from its topics.
 */
class LessonService
{
    public function addTopic(Lesson $lesson, string $title): LessonTopic
    {
        $position = (int) $lesson->topics()->max('position') + 1;

        return $lesson->topics()->create(['title' => $title, 'position' => $position]);
    }

    public function toggleTopic(LessonTopic $topic): LessonTopic
    {
        $complete = ! $topic->is_complete;

        $topic->update([
            'is_complete'  => $complete,
            'completed_on' => $complete ? now()->toDateString() : null,
        ]);

        return $topic;
    }
}
