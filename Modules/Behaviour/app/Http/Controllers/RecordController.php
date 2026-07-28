<?php

namespace Modules\Behaviour\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Student;
use Modules\Behaviour\Models\BehaviourRecord;
use Modules\Behaviour\Models\BehaviourType;
use Modules\Behaviour\Services\BehaviourService;

class RecordController extends Controller
{
    public function __construct(private readonly BehaviourService $service) {}

    public function index()
    {
        return view('behaviour::records.index', [
            'students' => Student::where('is_active', true)
                ->with(['liveRecord.schoolClass', 'liveRecord.section'])
                ->orderBy('full_name')->get(),
            'types'    => BehaviourType::active()->orderBy('title')->get(),
            'records'  => BehaviourRecord::with(['student.liveRecord.schoolClass', 'student.liveRecord.section', 'type'])
                ->latest('incident_date')->latest('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'        => ['required', 'exists:students,id'],
            'behaviour_type_id' => ['required', 'exists:behaviour_types,id'],
            'incident_date'     => ['required', 'date'],
            'comment'           => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->record(
            (int) $data['student_id'],
            (int) $data['behaviour_type_id'],
            $data['incident_date'],
            $data['comment'] ?? null,
        );

        return redirect()->route('behaviour.records.index')->with('status', 'Behaviour recorded.');
    }

    public function destroy(BehaviourRecord $record)
    {
        $record->delete();

        return redirect()->back()->with('status', 'Removed.');
    }
}
