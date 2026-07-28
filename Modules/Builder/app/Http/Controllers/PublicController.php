<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Builder\Services\BuilderService;

/** Renders the public website from Builder data. No auth. */
class PublicController extends Controller
{
    public function home(BuilderService $builder)
    {
        $page = $builder->homePage();

        if (! $page) {
            return view('builder::public.fallback', ['settings' => $builder->settings()]);
        }

        return view('builder::public.page', compact('page'));
    }

    public function page(string $slug, BuilderService $builder)
    {
        $page = $builder->pageBySlug($slug);
        abort_if($page === null, 404);

        return view('builder::public.page', compact('page'));
    }
}
