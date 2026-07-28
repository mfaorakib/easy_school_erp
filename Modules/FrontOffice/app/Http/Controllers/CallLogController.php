<?php

namespace Modules\FrontOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FrontOffice\Models\PhoneCallLog;

class CallLogController extends Controller
{
    public function index()
    {
        $logs = PhoneCallLog::latest('call_date')->latest('id')->get();

        return view('frontoffice::call-logs.index', compact('logs'));
    }

    public function create()
    {
        return view('frontoffice::call-logs.form', ['log' => new PhoneCallLog]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        PhoneCallLog::create($data);

        return redirect()->route('frontoffice.callLogs.index')->with('status', 'Call log saved.');
    }

    public function edit(PhoneCallLog $callLog)
    {
        return view('frontoffice::call-logs.form', ['log' => $callLog]);
    }

    public function update(Request $request, PhoneCallLog $callLog)
    {
        $callLog->update($this->validated($request));

        return redirect()->route('frontoffice.callLogs.index')->with('status', 'Call log saved.');
    }

    public function destroy(PhoneCallLog $callLog)
    {
        $callLog->delete();

        return redirect()->route('frontoffice.callLogs.index')->with('status', 'Call log saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'phone'               => ['required', 'string', 'max:40'],
            'call_type'           => ['required', 'in:incoming,outgoing'],
            'call_date'           => ['required', 'date'],
            'call_duration'       => ['nullable', 'string', 'max:40'],
            'description'         => ['nullable', 'string'],
            'next_follow_up_date' => ['nullable', 'date'],
            'note'                => ['nullable', 'string', 'max:255'],
        ]);
    }
}
