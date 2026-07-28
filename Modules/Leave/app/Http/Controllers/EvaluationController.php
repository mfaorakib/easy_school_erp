<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResource\Models\Staff;
use Modules\Leave\Models\TeacherEvaluation;
use Modules\Leave\Services\LeaveService;

class EvaluationController extends Controller
{
    public function index()
    {
        $evaluations = TeacherEvaluation::with('staff')->latest('evaluation_date')->latest('id')->get();

        return view('leave::evaluations.index', compact('evaluations'));
    }

    public function create()
    {
        return view('leave::evaluations.form', [
            'evaluation' => new TeacherEvaluation,
            'teachers'   => Staff::teachers()->get(),
        ]);
    }

    public function store(Request $request, LeaveService $leave)
    {
        $data = $this->validated($request);

        $leave->evaluate($data);

        return redirect()->route('leave.evaluations.index')->with('status', 'Evaluation saved.');
    }

    public function edit(TeacherEvaluation $evaluation)
    {
        return view('leave::evaluations.form', [
            'evaluation' => $evaluation,
            'teachers'   => Staff::teachers()->get(),
        ]);
    }

    public function update(Request $request, TeacherEvaluation $evaluation)
    {
        $data = $this->validated($request);

        $criteria = array_values(array_filter($data['criteria'] ?? [], fn ($c) => ! empty($c['name'])));
        $scores = array_map(fn ($c) => (float) ($c['score'] ?? 0), $criteria);
        $total = count($scores) ? round(array_sum($scores) / count($scores), 2) : 0;

        $evaluation->update([
            'staff_id'        => $data['staff_id'],
            'term'            => $data['term'],
            'evaluation_date' => $data['evaluation_date'],
            'criteria'        => $criteria,
            'total_score'     => $total,
            'remarks'         => $data['remarks'],
        ]);

        return redirect()->route('leave.evaluations.index')->with('status', 'Evaluation saved.');
    }

    public function destroy(TeacherEvaluation $evaluation)
    {
        $evaluation->delete();

        return redirect()->route('leave.evaluations.index')->with('status', 'Evaluation saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'staff_id'        => ['required', 'exists:staff,id'],
            'term'            => ['nullable', 'string', 'max:100'],
            'evaluation_date' => ['required', 'date'],
            'remarks'         => ['nullable', 'string', 'max:500'],
            'criteria'        => ['array'],
            'criteria.*.name' => ['nullable', 'string', 'max:120'],
            'criteria.*.score' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);
    }
}
