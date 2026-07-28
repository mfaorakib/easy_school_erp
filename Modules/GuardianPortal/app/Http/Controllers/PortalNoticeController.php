<?php

namespace Modules\GuardianPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Communication\Models\Event;
use Modules\Communication\Models\Notice;

class PortalNoticeController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->getRoleNames()->first() ?? 'all';

        $notices = Notice::published()
            ->forRole($role)
            ->latest('notice_date')
            ->get();

        $events = Event::upcoming()->get();

        return view('guardianportal::notices', compact('notices', 'events'));
    }
}
