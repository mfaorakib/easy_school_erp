<?php

namespace Modules\Transport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Student;
use Modules\Transport\Models\StudentTransport;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;
use Modules\Transport\Services\TransportService;

class StudentTransportController extends Controller
{
    public function __construct(private readonly TransportService $service) {}

    public function index()
    {
        return view('transport::students.index', [
            'students'    => Student::where('is_active', true)->orderBy('full_name')->get(),
            'routes'      => TransportRoute::where('is_active', true)->orderBy('name')->get(),
            'vehicles'    => Vehicle::where('is_active', true)->orderBy('vehicle_no')->get(),
            'assignments' => StudentTransport::with(['student', 'route', 'vehicle'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'         => ['required', 'exists:students,id'],
            'transport_route_id' => ['required', 'exists:transport_routes,id'],
            'vehicle_id'         => ['nullable', 'exists:vehicles,id'],
        ]);

        $this->service->assignStudent(
            (int) $data['student_id'],
            (int) $data['transport_route_id'],
            isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : null,
        );

        return redirect()->route('transport.students.index')->with('status', 'Student assigned.');
    }

    public function unassign(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required'],
        ]);

        $this->service->unassignStudent((int) $data['student_id']);

        return redirect()->back()->with('status', 'Removed.');
    }
}
