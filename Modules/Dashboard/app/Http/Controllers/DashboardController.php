<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboard\Models\Todo;
use Modules\Dashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        return view('dashboard::index', [
            'kpis'             => $dashboard->kpis(),
            'finance'          => $dashboard->finance(),
            'attendance'       => $dashboard->attendanceToday(),
            'attendanceTrend'  => $dashboard->attendanceTrend(),
            'studentsByClass'  => $dashboard->studentsByClass(),
            'notices'          => $dashboard->notices(),
            'events'           => $dashboard->upcomingEvents(),
            'activity'         => $dashboard->recentActivity(),
            'todos'            => Todo::forUser(auth()->id())->get(),
        ]);
    }

    public function storeTodo(Request $request)
    {
        $request->validate(['title' => ['required', 'string', 'max:255']]);

        Todo::create([
            'user_id'  => auth()->id(),
            'title'    => $request->title,
            'position' => (int) Todo::where('user_id', auth()->id())->max('position') + 1,
        ]);

        return back();
    }

    public function toggleTodo(Todo $todo)
    {
        abort_unless($todo->user_id === auth()->id(), 403);
        $todo->update(['is_done' => ! $todo->is_done]);

        return back();
    }

    public function destroyTodo(Todo $todo)
    {
        abort_unless($todo->user_id === auth()->id(), 403);
        $todo->delete();

        return back();
    }
}
