<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Models\Holiday;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderByDesc('from_date')->get();

        return view('settings::holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('settings::holidays.form', ['holiday' => new Holiday]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Holiday::create($data);

        return redirect()->route('settings.holidays.index')->with('status', 'Holiday saved.');
    }

    public function edit(Holiday $holiday)
    {
        return view('settings::holidays.form', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $holiday->update($this->validated($request));

        return redirect()->route('settings.holidays.index')->with('status', 'Holiday saved.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('settings.holidays.index')->with('status', 'Holiday deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'from_date'   => ['required', 'date'],
            'to_date'     => ['required', 'date', 'after_or_equal:from_date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
