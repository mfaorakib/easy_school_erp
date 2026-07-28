<?php

namespace Modules\StaffPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\HumanResource\Models\ResignationApplication;
use Modules\HumanResource\Services\ResignationService;
use Modules\StaffPortal\Services\StaffPortalService;

/** Staff self-service — submit and track my own resignation requests. */
class PortalResignationController extends Controller
{
    public function __construct(protected StaffPortalService $portal)
    {
    }

    public function index(Request $request): View
    {
        $staff = $this->portal->assertStaff($request->user());

        $applications = ResignationApplication::where('staff_id', $staff->id)->latest('id')->get();

        return view('staffportal::resignation.index', compact('staff', 'applications'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $staff = $this->portal->assertStaff($request->user());

        $hasPending = ResignationApplication::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()
                ->route('staffportal.resignation.index')
                ->with('error', 'You already have a pending resignation request.');
        }

        return view('staffportal::resignation.create', compact('staff'));
    }

    public function store(Request $request, ResignationService $service): RedirectResponse
    {
        $staff = $this->portal->assertStaff($request->user());

        $data = $request->validate([
            'intended_last_day' => ['required', 'date', 'after:today'],
            'reason'             => ['nullable', 'string', 'max:500'],
        ]);

        $service->apply($staff->id, $data['intended_last_day'], $data['reason'] ?? null);

        return redirect()
            ->route('staffportal.resignation.index')
            ->with('status', 'Resignation request submitted.');
    }
}
