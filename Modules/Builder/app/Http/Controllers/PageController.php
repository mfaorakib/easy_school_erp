<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\CmsPage;
use Modules\Builder\Services\BuilderService;

class PageController extends Controller
{
    public function index()
    {
        $pages = CmsPage::orderByDesc('is_home')->orderBy('title')->get();

        return view('builder::pages.index', compact('pages'));
    }

    public function create()
    {
        return view('builder::pages.form', ['page' => new CmsPage]);
    }

    public function store(Request $request, BuilderService $builder)
    {
        $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $builder->uniqueSlug($request->title);

        $page = CmsPage::create([
            'title'            => $request->title,
            'slug'             => $slug,
            'is_published'     => $request->boolean('is_published'),
            'meta_description' => $request->meta_description,
        ]);

        return redirect()->route('builder.blocks.index', $page)
            ->with('status', 'Page created — add content blocks.');
    }

    public function edit(CmsPage $page)
    {
        return view('builder::pages.form', compact('page'));
    }

    public function update(Request $request, CmsPage $page, BuilderService $builder)
    {
        $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'slug'             => ['nullable', 'string'],
        ]);

        $slug = $request->filled('slug')
            ? $builder->uniqueSlug($request->slug, $page->id)
            : $page->slug;

        $page->update([
            'title'            => $request->title,
            'slug'             => $slug,
            'is_published'     => $request->boolean('is_published'),
            'meta_description' => $request->meta_description,
        ]);

        return redirect()->route('builder.pages.index')->with('status', 'Page updated.');
    }

    public function destroy(CmsPage $page)
    {
        $page->delete();

        return redirect()->route('builder.pages.index')->with('status', 'Page deleted.');
    }

    public function makeHome(CmsPage $page, BuilderService $builder)
    {
        $builder->markHome($page);

        return redirect()->back()->with('status', 'Home page set.');
    }
}
