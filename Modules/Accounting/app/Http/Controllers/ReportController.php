<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Services\AccountingService;

class ReportController extends Controller
{
    public function summary(Request $request, AccountingService $accounting)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $data = $accounting->summary($from, $to);

        return view('accounting::summary', compact('data', 'from', 'to'));
    }
}
