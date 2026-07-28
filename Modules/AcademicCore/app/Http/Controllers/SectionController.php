<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Section;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::orderBy('name')->get();

        return view('academiccore::sections.index', compact('sections'));
    }

    public function create()
    {
        return view('academiccore::sections.form', ['section' => new Section]);
    }

    public function store(Request $request)
    {
        Section::create($this->validated($request));

        return redirect()->route('academic.sections.index')->with('status', 'Section created.');
    }

    public function edit(Section $section)
    {
        return view('academiccore::sections.form', compact('section'));
    }

    public function update(Request $request, Section $section)
    {
        $section->update($this->validated($request));

        return redirect()->route('academic.sections.index')->with('status', 'Section updated.');
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('academic.sections.index')->with('status', 'Section deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
