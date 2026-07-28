<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSeatPlan;
use Modules\Examination\Services\SeatPlanService;
use Modules\Timetable\Models\Classroom;

class SeatPlanController extends Controller
{
    public function __construct(private readonly SeatPlanService $service) {}

    public function index()
    {
        $exams = Exam::active()->orderBy('name')->get();
        $classrooms = Classroom::active()->orderBy('room_no')->get();
        $plans = ExamSeatPlan::with('exam')->get()->keyBy('exam_id');

        return view('examination::seat_plan.index', compact('exams', 'classrooms', 'plans'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'exam_id'          => ['required', 'integer', 'exists:exams,id'],
            'classroom_ids'    => ['required', 'array', 'min:1'],
            'classroom_ids.*'  => ['integer', 'exists:classrooms,id'],
            'seats_per_bench'  => ['required', 'integer', 'min:1', 'max:4'],
            'mix_classes'      => ['nullable'],
        ]);

        $exam = Exam::findOrFail($data['exam_id']);
        $result = $this->service->generate($exam, $data['classroom_ids'], (int) $data['seats_per_bench'], $request->boolean('mix_classes'));

        $status = "Seat plan generated — {$result['seated']} of {$result['total']} students seated.";
        if ($result['unseated'] > 0) {
            $status .= " {$result['unseated']} could not be seated — add more rooms or capacity.";
        }

        return redirect()->route('exam.seat_plan.show', $exam)->with('status', $status);
    }

    public function show(Exam $exam)
    {
        $plan = ExamSeatPlan::where('exam_id', $exam->id)->with('rooms.classroom')->first();

        $rooms = [];
        if ($plan) {
            foreach ($plan->rooms as $planRoom) {
                $rooms[] = [
                    'classroom'   => $planRoom->classroom,
                    'assignments' => $this->service->assignmentsForRoom($plan, $planRoom->classroom_id),
                ];
            }
        }

        return view('examination::seat_plan.show', compact('exam', 'plan', 'rooms'));
    }

    public function destroy(Exam $exam)
    {
        ExamSeatPlan::where('exam_id', $exam->id)->delete();

        return redirect()->route('exam.seat_plan.index')->with('status', 'Seat plan cleared.');
    }
}
