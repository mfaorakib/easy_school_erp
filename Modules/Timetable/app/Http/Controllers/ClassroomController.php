<?php

namespace Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Timetable\Models\Classroom;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::orderBy('room_no')->get();

        return view('timetable::classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('timetable::classrooms.form', ['classroom' => new Classroom]);
    }

    public function store(Request $request)
    {
        Classroom::create($this->validated($request));

        return redirect()->route('timetable.classrooms.index')->with('status', 'Classroom saved.');
    }

    public function edit(Classroom $classroom)
    {
        return view('timetable::classrooms.form', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $classroom->update($this->validated($request));

        return redirect()->route('timetable.classrooms.index')->with('status', 'Classroom saved.');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('timetable.classrooms.index')->with('status', 'Classroom deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'room_no'  => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
