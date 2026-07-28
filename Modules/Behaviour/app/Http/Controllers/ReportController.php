<?php

namespace Modules\Behaviour\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Behaviour\Services\BehaviourService;

class ReportController extends Controller
{
    /**
     * Show per-student behaviour point totals (route: behaviour.report.index).
     */
    public function index()
    {
        $totals = app(BehaviourService::class)->studentTotals();

        return view('behaviour::report.index', compact('totals'));
    }
}
