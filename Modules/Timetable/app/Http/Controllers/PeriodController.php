<?php

namespace Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Timetable\Models\ClassPeriod;

class PeriodController extends Controller
{
    public function index()
    {
        $periods = ClassPeriod::ordered()->get();

        return view('timetable::periods.index', compact('periods'));
    }

    public function create()
    {
        return view('timetable::periods.form', ['period' => new ClassPeriod]);
    }

    public function store(Request $request)
    {
        ClassPeriod::create($this->validated($request));

        return redirect()->route('timetable.periods.index')->with('status', 'Period saved.');
    }

    public function edit(ClassPeriod $period)
    {
        return view('timetable::periods.form', compact('period'));
    }

    public function update(Request $request, ClassPeriod $period)
    {
        $period->update($this->validated($request));

        return redirect()->route('timetable.periods.index')->with('status', 'Period saved.');
    }

    public function destroy(ClassPeriod $period)
    {
        $period->delete();

        return redirect()->route('timetable.periods.index')->with('status', 'Period deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'start_time' => ['required'],
            'end_time'   => ['required'],
            'position'   => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_break']  = $request->boolean('is_break');
        $data['is_active'] = $request->boolean('is_active');
        $data['position']  = $request->input('position', 0);

        return $data;
    }
}
