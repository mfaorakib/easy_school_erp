<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\BankAccount;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\PayrollService;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period');
        $status = $request->input('status');

        $payslips = Payslip::with('staff')
            ->when($period, fn ($q) => $q->where('period', $period))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('period')
            ->orderBy('id')
            ->get();

        $periods = Payslip::select('period')->distinct()->orderByDesc('period')->pluck('period');

        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('payroll::payslips.index', compact('payslips', 'periods', 'period', 'status', 'bankAccounts'));
    }

    public function show(Payslip $payslip)
    {
        $payslip->load('items', 'staff');

        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('payroll::payslips.show', compact('payslip', 'bankAccounts'));
    }

    public function markPaid(Request $request, Payslip $payslip, PayrollService $payroll)
    {
        $request->validate([
            'payment_method' => ['nullable', 'string', 'max:30'],
            'bank_account_id' => ['nullable', 'integer', 'exists:bank_accounts,id'],
        ]);

        $payroll->markPaid(
            $payslip,
            $request->input('payment_method', 'cash'),
            null,
            $request->input('bank_account_id'),
        );

        return redirect()->route('payroll.payslips.show', $payslip)->with('status', 'Marked paid.');
    }

    public function destroy(Payslip $payslip)
    {
        // A paid payslip has already committed its salary-advance recovery
        // (incremented recovered_amount on the advances) and posted its expense
        // to accounting. Deleting it would leave the advance showing repaid
        // while the recovering payslip is gone — silently desyncing what the
        // staff still owes. Only unpaid (generated) payslips may be deleted.
        if ($payslip->isPaid()) {
            return redirect()->route('payroll.payslips.index')
                ->with('error', 'A paid payslip cannot be deleted — its advance recovery and accounting entry are already committed.');
        }

        $payslip->delete();

        return redirect()->route('payroll.payslips.index')->with('status', 'Payslip deleted.');
    }

    public function bulkPay(Request $request, PayrollService $payroll)
    {
        $request->validate([
            'payslip_ids'   => ['required', 'array', 'min:1'],
            'payslip_ids.*' => ['integer'],
            'payment_method' => ['required', 'in:cash,bank'],
            'bank_account_id' => ['nullable', 'integer', 'exists:bank_accounts,id'],
            'paid_on' => ['nullable', 'date'],
        ]);

        $count = $payroll->bulkMarkPaid(
            $request->input('payslip_ids'),
            $request->input('payment_method'),
            $request->input('payment_method') === 'bank' ? $request->input('bank_account_id') : null,
            $request->input('paid_on'),
        );

        return redirect()->route('payroll.payslips.index', $request->only('period'))
            ->with('status', $count.' payslip(s) marked paid.');
    }

    public function bankAdvice(Request $request, PayrollService $payroll)
    {
        $period = $request->input('period') ?: now()->format('Y-m');
        $list = $payroll->bankAdviceList($period);

        return view('payroll::payslips.bank-advice', compact('list', 'period'));
    }
}
