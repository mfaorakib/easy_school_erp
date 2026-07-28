<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Documents\Models\CertificateTemplate;
use Modules\Documents\Services\DocumentService;
use Modules\HumanResource\Models\Staff;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $templates = CertificateTemplate::active()->orderBy('name')->get();

        $templateId = $request->integer('template_id') ?: null;
        $template = $templateId ? CertificateTemplate::find($templateId) : null;

        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $students = collect();
        $staff = collect();

        if ($template && $template->holder_type === CertificateTemplate::HOLDER_STUDENT) {
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

        if ($template && $template->holder_type === CertificateTemplate::HOLDER_STAFF) {
            // Not filtered to active-only: an Experience Certificate is
            // typically issued to someone who has just LEFT (is_active
            // false), so they must still be pickable here.
            $staff = Staff::orderByDesc('is_active')->orderBy('full_name')->get();
        }

        $classes = SchoolClass::active()->orderBy('name')->get();
        $sections = Section::active()->orderBy('name')->get();

        return view('documents::certificates.index', compact(
            'templates',
            'template',
            'templateId',
            'classId',
            'sectionId',
            'students',
            'staff',
            'classes',
            'sections'
        ));
    }

    public function generate(Request $request, DocumentService $documents)
    {
        $request->validate([
            'template_id'  => ['required', 'exists:certificate_templates,id'],
            'holder_ids'   => ['array'],
            'holder_ids.*' => ['integer'],
        ]);

        $template = CertificateTemplate::findOrFail($request->template_id);
        $ids = $request->input('holder_ids', []);

        if ($template->holder_type === CertificateTemplate::HOLDER_GENERAL || empty($ids)) {
            $docs = [[
                'name' => '',
                'body' => $documents->resolveCertificate($template, null),
            ]];
        } else {
            $holders = $template->holder_type === CertificateTemplate::HOLDER_STAFF
                ? Staff::whereIn('id', $ids)->get()
                : Student::whereIn('id', $ids)->get();

            $docs = $holders->map(fn ($h) => [
                'name' => $documents->holderName($h),
                'body' => $documents->resolveCertificate($template, $h),
            ])->all();
        }

        return view('documents::print.certificate', [
            'template'  => $template,
            'documents' => $docs,
        ]);
    }
}
