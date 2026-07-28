<?php

namespace Modules\GuardianPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\GuardianPortal\Services\GuardianPortalService;
use Modules\Settings\Models\PaymentMethod;

/**
 * Guardian-only dashboard: the children list and each child's fee ledger.
 * Every specific-child lookup goes through GuardianPortalService::assertOwnsChild()
 * so a guardian can never view another family's data by guessing a student id.
 */
class PortalDashboardController extends Controller
{
    public function __construct(private readonly GuardianPortalService $portal) {}

    public function index(Request $request)
    {
        $children = $this->portal->children($request->user());

        $summaries = $children->mapWithKeys(function ($student) {
            $ledger = $this->portal->feeLedger($student);
            $totalDue = collect($ledger)->sum(fn ($row) => $row['balance']['due']);

            return [$student->id => ['due' => $totalDue, 'count' => count($ledger)]];
        });

        return view('guardianportal::dashboard', compact('children', 'summaries'));
    }

    public function child(Request $request)
    {
        $student = $this->portal->assertOwnsChild($request->user(), (int) $request->route('student'));
        $ledger = $this->portal->feeLedger($student);
        $paymentMethods = PaymentMethod::active()->get();

        return view('guardianportal::child', compact('student', 'ledger', 'paymentMethods'));
    }
}
