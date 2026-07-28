<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Section;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeeMaster;
use Modules\Fees\Services\FeeService;

class FeeAssignController extends Controller
{
    public function __construct(private readonly FeeService $service) {}

    public function index()
    {
        $masters = FeeMaster::with(['group', 'type', 'schoolClass'])
            ->withCount('assignments')
            ->latest()->get();

        return view('fees::assign.index', [
            'masters'  => $masters,
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fee_master_id' => ['required', 'integer', 'exists:fee_masters,id'],
            'section_id'    => ['nullable', 'integer', 'exists:sections,id'],
        ]);

        $master = FeeMaster::findOrFail($data['fee_master_id']);
        $count  = $this->service->assignToClass($master, $data['section_id'] ?? null);

        return redirect()->route('fees.assign.index')
            ->with('status', "Assigned to {$count} student(s).");
    }
}
