<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Examination\Models\GradeScale;

class GradeScaleController extends Controller
{
    public function index()
    {
        return view('examination::grades.index', ['grades' => GradeScale::orderByDesc('mark_from')->get()]);
    }

    public function create()
    {
        return view('examination::grades.form', ['grade' => new GradeScale]);
    }

    public function store(Request $request)
    {
        GradeScale::create($this->validated($request));

        return redirect()->route('exam.grades.index')->with('status', 'Grade created.');
    }

    public function edit(GradeScale $grade)
    {
        return view('examination::grades.form', compact('grade'));
    }

    public function update(Request $request, GradeScale $grade)
    {
        $grade->update($this->validated($request));

        return redirect()->route('exam.grades.index')->with('status', 'Grade updated.');
    }

    public function destroy(GradeScale $grade)
    {
        $grade->delete();

        return redirect()->route('exam.grades.index')->with('status', 'Grade deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:20'],
            'gpa'       => ['required', 'numeric', 'min:0'],
            'mark_from' => ['required', 'numeric', 'min:0'],
            'mark_upto' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
