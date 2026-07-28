<?php

namespace Modules\Homework\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Homework\Models\Homework;
use Modules\Homework\Services\HomeworkService;

class EvaluationController extends Controller
{
    public function __construct(private readonly HomeworkService $service) {}

    public function index(Request $request)
    {
        $homeworkId = $request->integer('homework_id') ?: null;

        $homeworks = Homework::with(['schoolClass', 'section', 'subject'])->latest()->get();
        $homework  = $homeworkId ? Homework::find($homeworkId) : null;
        $roster    = $homework ? $this->service->evaluationRoster($homework) : [];

        return view('homework::evaluate.index', compact('homeworks', 'homework', 'roster'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'homework_id' => ['required', 'exists:homeworks,id'],
            'complete'    => ['array'],
            'marks'       => ['array'],
            'comment'     => ['array'],
        ]);

        $studentIds = array_unique(array_merge(
            array_keys($request->input('marks', [])),
            array_keys($request->input('comment', [])),
            array_keys($request->input('complete', [])),
        ));

        $rows = [];
        foreach ($studentIds as $id) {
            $rows[$id] = [
                'complete' => ! empty($request->input("complete.$id")),
                'marks'    => $request->input("marks.$id"),
                'comment'  => $request->input("comment.$id"),
            ];
        }

        $this->service->saveEvaluation(Homework::findOrFail($data['homework_id']), $rows);

        return redirect()->route('homework.evaluate.index', ['homework_id' => $data['homework_id']])
            ->with('status', 'Evaluation saved.');
    }
}
