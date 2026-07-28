<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('name')->get();

        return view('academiccore::subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('academiccore::subjects.form', ['subject' => new Subject]);
    }

    public function store(Request $request)
    {
        Subject::create($this->validated($request));

        return redirect()->route('academic.subjects.index')->with('status', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        return view('academiccore::subjects.form', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $subject->update($this->validated($request));

        return redirect()->route('academic.subjects.index')->with('status', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('academic.subjects.index')->with('status', 'Subject deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'code'      => ['nullable', 'string', 'max:50'],
            'type'      => ['required', 'in:theory,practical'],
            'pass_mark' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
