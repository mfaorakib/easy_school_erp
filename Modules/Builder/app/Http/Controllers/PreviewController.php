<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Builder\Models\CmsPage;

/** Renders a page (published or draft) for the builder's live preview pane. */
class PreviewController extends Controller
{
    public function show(CmsPage $page)
    {
        $page->load('visibleBlocks');

        return view('builder::public.page', ['page' => $page, 'preview' => true]);
    }
}
