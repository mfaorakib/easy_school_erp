<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Student;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeeCarryForward;
use Modules\Fees\Services\FeeService;
use Modules\Settings\Models\PaymentMethod;

class FeeCollectController extends Controller
{
    public function __construct(private readonly FeeService $service) {}

    public function index(Request $request)
    {
        $q         = trim((string) $request->get('q'));
        $studentId = $request->integer('student_id') ?: null;

        $results = collect();
        if ($q !== '') {
            $results = Student::where('is_active', true)
                ->where(fn ($w) => $w->where('full_name', 'like', "%$q%")->orWhere('admission_no', $q))
                ->limit(20)->get();
        }

        $student = $studentId ? Student::find($studentId) : null;
        $ledger  = $student ? $this->service->studentLedger($student->id) : [];
        $carry   = $student ? FeeCarryForward::where('student_id', $student->id)->get() : collect();
        $paymentMethods = PaymentMethod::active()->get();

        return view('fees::collect.index', compact('q', 'results', 'student', 'ledger', 'carry', 'paymentMethods'));
    }

    public function collect(Request $request)
    {
        $data = $request->validate([
            'fee_assignment_id' => ['required', 'integer', 'exists:fee_assignments,id'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'fine'              => ['nullable', 'numeric', 'min:0'],
            'discount'          => ['nullable', 'numeric', 'min:0'],
            'method'            => ['required', 'string', 'max:30'],
            'paid_on'           => ['required', 'date'],
            'reference'         => ['nullable', 'string', 'max:100'],
            'note'              => ['nullable', 'string', 'max:255'],
        ]);

        $assignment = FeeAssignment::findOrFail($data['fee_assignment_id']);
        $this->service->collect($assignment, $data);

        return redirect()
            ->route('fees.collect.index', ['student_id' => $assignment->student_id])
            ->with('status', 'Payment collected.');
    }
}
