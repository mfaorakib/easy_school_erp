<?php

namespace Modules\Printing\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Examination\Models\Exam;
use Modules\Printing\Services\PrintService;
use Modules\Timetable\Models\Classroom;

class SeatPlanPrintController extends Controller
{
    public function __construct(private readonly PrintService $service) {}

    public function index()
    {
        $exams = Exam::active()->orderBy('name')->get();

        return view('printing::seat_plan.index', compact('exams'));
    }

    public function room(Exam $exam, Classroom $classroom)
    {
        $data = $this->service->seatPlan($exam->id, $classroom->id);

        return view('printing::seat_plan.room', $data);
    }

    public function all(Exam $exam)
    {
        $data = $this->service->seatPlan($exam->id);

        return view('printing::seat_plan.all', $data);
    }
}
