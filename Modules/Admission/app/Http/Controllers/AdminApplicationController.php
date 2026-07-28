<?php

namespace Modules\Admission\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admission\Models\AdmissionApplication;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $applications = AdmissionApplication::with('desiredClass')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admission::applications.index', compact('applications', 'status'));
    }

    public function show(AdmissionApplication $application)
    {
        $application->load(['desiredClass', 'gender', 'reviewer', 'student', 'documents.type']);
        $classes = SchoolClass::active()->orderBy('name')->get();
        $sections = Section::active()->orderBy('name')->get();

        return view('admission::applications.show', compact('application', 'classes', 'sections'));
    }
}
