<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Documents\Models\IdCardTemplate;
use Modules\Documents\Services\DocumentService;
use Modules\HumanResource\Models\Staff;

class IdCardController extends Controller
{
    public function index(Request $request)
    {
        $templates = IdCardTemplate::active()->orderBy('name')->get();

        $templateId = $request->integer('template_id') ?: null;
        $template = $templateId ? IdCardTemplate::find($templateId) : null;

        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $students = collect();
        $staff = collect();

        if ($template && $template->holder_type === 'student') {
            $students = StudentRecord::live()
                ->when($classId, fn ($q) => $q->where('class_id', $classId))
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->with('student')
                ->orderBy('roll_no')
                ->get()
                ->pluck('student')
                ->filter()
                ->values();
        }

        if ($template && $template->holder_type === 'staff') {
            $staff = Staff::where('is_active', true)->orderBy('full_name')->get();
        }

        return view('documents::id_cards.index', compact('templates', 'template', 'templateId', 'classId', 'sectionId', 'students', 'staff') + [
            'classes' => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function generate(Request $request, DocumentService $documents)
    {
        $request->validate([
            'template_id' => ['required', 'exists:id_card_templates,id'],
            'holder_ids' => ['required', 'array', 'min:1'],
            'holder_ids.*' => ['integer'],
        ]);

        $template = IdCardTemplate::findOrFail($request->template_id);

        $ids = $request->input('holder_ids', []);

        $holders = $template->holder_type === 'staff'
            ? Staff::whereIn('id', $ids)->get()
            : Student::whereIn('id', $ids)->get();

        $cards = $holders->map(fn ($h) => $documents->idCardData($template, $h))->all();

        return view('documents::print.id_cards', compact('template', 'cards'));
    }
}
