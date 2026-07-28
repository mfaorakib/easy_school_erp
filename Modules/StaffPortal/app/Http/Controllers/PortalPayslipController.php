<?php

namespace Modules\StaffPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Payroll\Models\Payslip;
use Modules\StaffPortal\Services\StaffPortalService;

/**
 * A staff member's own payslips. Every lookup is scoped to the logged-in
 * user's own Staff record via StaffPortalService::assertStaff() first, and
 * show() additionally checks the payslip actually belongs to that staff
 * member, so nobody can view another staff member's payslip by guessing an
 * id in the URL.
 */
class PortalPayslipController extends Controller
{
    public function __construct(
        private readonly StaffPortalService $portal,
    ) {}

    public function index(Request $request)
    {
        $staff = $this->portal->assertStaff($request->user());

        $payslips = Payslip::where('staff_id', $staff->id)
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->get();

        return view('staffportal::payslips.index', compact('staff', 'payslips'));
    }

    public function show(Request $request, Payslip $payslip)
    {
        $staff = $this->portal->assertStaff($request->user());
        abort_if($payslip->staff_id !== $staff->id, 403);

        $payslip->load('items');

        return view('staffportal::payslips.show', compact('payslip'));
    }
}
