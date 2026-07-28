<?php

namespace Modules\StaffPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Payroll\Models\SalaryAdvance;
use Modules\Payroll\Services\SalaryAdvanceService;
use Modules\StaffPortal\Services\StaffPortalService;

/** Staff self-service — submit and track my own salary advance requests. */
class PortalAdvanceController extends Controller
{
    public function __construct(protected StaffPortalService $portal)
    {
    }

    public function index(Request $request, SalaryAdvanceService $service): View
    {
        $staff = $this->portal->assertStaff($request->user());

        $advances = SalaryAdvance::where('staff_id', $staff->id)->latest('id')->get();
        $outstanding = $service->outstandingFor($staff->id);

        return view('staffportal::advances.index', compact('staff', 'advances', 'outstanding'));
    }

    public function create(Request $request): View
    {
        $staff = $this->portal->assertStaff($request->user());

        return view('staffportal::advances.create', compact('staff'));
    }

    public function store(Request $request, SalaryAdvanceService $service): RedirectResponse
    {
        $staff = $this->portal->assertStaff($request->user());

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $service->request($staff->id, (float) $data['amount'], $data['reason'] ?? null);

        return redirect()
            ->route('staffportal.advances.index')
            ->with('status', 'Salary advance request submitted.');
    }
}
