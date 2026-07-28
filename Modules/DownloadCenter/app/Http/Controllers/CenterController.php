<?php

namespace Modules\DownloadCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DownloadCenter\Models\DownloadContent;

class CenterController extends Controller
{
    public function index()
    {
        $role = auth()->user()->getRoleNames()->first() ?? 'all';

        $contents = DownloadContent::published()
            ->with(['type', 'schoolClass', 'section'])
            ->when($role !== 'super-admin', fn ($q) => $q->forRole($role))
            ->latest('publish_date')
            ->latest('id')
            ->get();

        return view('downloadcenter::center.index', compact('contents', 'role'));
    }
}
