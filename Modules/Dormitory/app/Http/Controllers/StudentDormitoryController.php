<?php

namespace Modules\Dormitory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Student;
use Modules\Dormitory\Models\DormitoryRoom;
use Modules\Dormitory\Models\StudentDormitory;
use Modules\Dormitory\Services\DormitoryService;
use RuntimeException;

class StudentDormitoryController extends Controller
{
    public function __construct(private readonly DormitoryService $service) {}

    public function index()
    {
        return view('dormitory::students.index', [
            'students'    => Student::where('is_active', true)->orderBy('full_name')->get(),
            'rooms'       => DormitoryRoom::where('is_active', true)->with('dormitory')->get(),
            'allocations' => StudentDormitory::with(['student', 'room.dormitory'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'         => ['required', 'exists:students,id'],
            'dormitory_room_id'  => ['required', 'exists:dormitory_rooms,id'],
        ]);

        try {
            $this->service->assignStudent(
                (int) $data['student_id'],
                (int) $data['dormitory_room_id'],
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->route('dormitory.students.index')->with('status', 'Student allocated.');
    }

    public function unassign(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required'],
        ]);

        $this->service->unassignStudent((int) $data['student_id']);

        return redirect()->back()->with('status', 'Removed.');
    }
}
