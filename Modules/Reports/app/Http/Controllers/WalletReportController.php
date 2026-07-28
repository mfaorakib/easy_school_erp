<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Reports\Services\ReportService;

class WalletReportController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $data = $service->walletReport($from, $to);

        return view('reports::wallet', compact('data', 'from', 'to'));
    }
}
