<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Services\ClassService;

class ClassController extends Controller
{
    public function __construct(private readonly ClassService $service) {}

    public function index()
    {
        $classes = SchoolClass::with('sections')->orderBy('position')->orderBy('name')->get();

        return view('academiccore::classes.index', compact('classes'));
    }

    public function create()
    {
        return view('academiccore::classes.form', [
            'class'    => new SchoolClass,
            'sections' => Section::active()->orderBy('name')->get(),
            'selected' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->service->create($data, $request->input('sections', []));

        return redirect()->route('academic.classes.index')->with('status', 'Class created.');
    }

    public function edit(SchoolClass $class)
    {
        return view('academiccore::classes.form', [
            'class'    => $class,
            'sections' => Section::active()->orderBy('name')->get(),
            'selected' => $class->classSections()->pluck('section_id')->all(),
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $this->validated($request);
        $this->service->update($class, $data, $request->input('sections', []));

        return redirect()->route('academic.classes.index')->with('status', 'Class updated.');
    }

    public function destroy(SchoolClass $class)
    {
        try {
            $this->service->delete($class);
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('academic.classes.index')->with('status', 'Class deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'pass_mark'   => ['nullable', 'numeric', 'min:0'],
            'position'    => ['nullable', 'integer', 'min:0'],
            'sections'    => ['array'],
            'sections.*'  => ['integer', 'exists:sections,id'],
        ]);
    }
}
