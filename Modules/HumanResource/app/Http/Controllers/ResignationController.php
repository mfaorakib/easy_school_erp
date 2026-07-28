<?php

namespace Modules\HumanResource\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResource\Models\ResignationApplication;
use Modules\HumanResource\Services\ResignationService;

class ResignationController extends Controller
{
    public function index()
    {
        $pending = ResignationApplication::pending()
            ->with('staff')
            ->latest('id')
            ->get();

        $history = ResignationApplication::where('status', '!=', ResignationApplication::STATUS_PENDING)
            ->with(['staff', 'reviewer'])
            ->latest('reviewed_at')
            ->limit(20)
            ->get();

        return view('humanresource::resignations.index', compact('pending', 'history'));
    }

    public function review(Request $request, ResignationApplication $resignation, ResignationService $service)
    {
        $request->validate([
            'status'      => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:255'],
        ]);

        $service->review($resignation, $request->status, $request->review_note);

        return redirect()->route('hr.resignations.index')
            ->with('status', 'Resignation '.$request->status.'.');
    }
}
