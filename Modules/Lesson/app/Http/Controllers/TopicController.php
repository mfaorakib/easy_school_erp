<?php

namespace Modules\Lesson\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Lesson\Models\Lesson;
use Modules\Lesson\Models\LessonTopic;
use Modules\Lesson\Services\LessonService;

class TopicController extends Controller
{
    public function __construct(private readonly LessonService $service) {}

    public function index(Request $request)
    {
        $lessonId = $request->integer('lesson_id') ?: null;

        $lessons = Lesson::with(['schoolClass', 'section', 'subject'])->latest()->get();
        $lesson  = $lessonId ? Lesson::with('topics')->find($lessonId) : null;

        return view('lesson::topics.index', compact('lessons', 'lesson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => ['required', 'exists:lessons,id'],
            'title'     => ['required', 'string', 'max:200'],
        ]);

        $lesson = Lesson::findOrFail($request->integer('lesson_id'));

        $this->service->addTopic($lesson, $request->input('title'));

        return redirect()->route('lesson.topics.index', ['lesson_id' => $lesson->id])
            ->with('status', 'Topic added.');
    }

    public function toggle(LessonTopic $topic)
    {
        $this->service->toggleTopic($topic);

        return back()->with('status', 'Topic updated.');
    }

    public function destroy(LessonTopic $topic)
    {
        $lessonId = $topic->lesson_id;

        $topic->delete();

        return redirect()->route('lesson.topics.index', ['lesson_id' => $lessonId])
            ->with('status', 'Topic removed.');
    }
}
