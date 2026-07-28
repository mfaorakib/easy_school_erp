<?php

namespace Modules\Communication\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Communication\Models\Event;
use Modules\Communication\Models\Notice;

class NoticeBoardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->getRoleNames()->first() ?? 'all';

        $notices = Notice::published()
            ->when($role !== 'super-admin', fn ($q) => $q->forRole($role))
            ->latest('notice_date')
            ->get();

        $events = Event::upcoming()->get();

        return view('communication::board.index', compact('notices', 'events', 'role'));
    }
}
