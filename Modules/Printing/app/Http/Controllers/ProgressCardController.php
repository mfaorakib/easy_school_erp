<?php

namespace Modules\Printing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Printing\Services\PrintService;

class ProgressCardController extends Controller
{
    public function index(Request $request)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $students = ($classId && $sectionId)
            ? StudentRecord::live()
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->with('student')
                ->orderBy('roll_no')
                ->get()
                ->pluck('student')
                ->filter()
                ->values()
            : collect();

        return view('printing::progress', compact('classId', 'sectionId', 'students') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function generate(Request $request, PrintService $print)
    {
        $request->validate([
            'holder_ids'   => ['required', 'array', 'min:1'],
            'holder_ids.*' => ['integer'],
        ]);

        $cards = collect($request->input('holder_ids', []))
            ->map(fn ($sid) => $print->progressCard((int) $sid))
            ->all();

        return view('printing::print.progress', compact('cards'));
    }
}
