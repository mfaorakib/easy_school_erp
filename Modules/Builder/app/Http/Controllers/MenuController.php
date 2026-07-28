<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\CmsMenu;
use Modules\Builder\Models\CmsMenuItem;
use Modules\Builder\Models\CmsPage;

class MenuController extends Controller
{
    public function index()
    {
        $header = CmsMenu::firstOrCreate(['location' => 'header'], ['title' => 'Main Menu']);
        $footer = CmsMenu::firstOrCreate(['location' => 'footer'], ['title' => 'Footer Menu']);

        $header->load('items.page');
        $footer->load('items.page');

        $pages = CmsPage::orderBy('title')->get();

        return view('builder::menus.index', compact('header', 'footer', 'pages'));
    }

    public function addItem(Request $request, CmsMenu $menu)
    {
        $request->validate([
            'label'        => ['required', 'string', 'max:120'],
            'page_id'      => ['nullable', 'exists:cms_pages,id'],
            'url'          => ['nullable', 'string', 'max:255'],
            'open_new_tab' => ['nullable', 'boolean'],
        ]);

        $position = (int) $menu->items()->max('position') + 1;

        $menu->items()->create([
            'label'        => $request->label,
            'page_id'      => $request->page_id ?: null,
            'url'          => $request->url ?: null,
            'position'     => $position,
            'open_new_tab' => $request->boolean('open_new_tab'),
        ]);

        return redirect()->route('builder.menus.index')->with('status', 'Item added.');
    }

    public function updateItem(Request $request, CmsMenuItem $item)
    {
        $request->validate([
            'label'        => ['required', 'string', 'max:120'],
            'page_id'      => ['nullable', 'exists:cms_pages,id'],
            'url'          => ['nullable', 'string', 'max:255'],
            'open_new_tab' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'label'        => $request->label,
            'page_id'      => $request->page_id ?: null,
            'url'          => $request->url ?: null,
            'open_new_tab' => $request->boolean('open_new_tab'),
        ]);

        return redirect()->route('builder.menus.index')->with('status', 'Item updated.');
    }

    public function moveItem(Request $request, CmsMenuItem $item)
    {
        $dir = $request->input('direction', 'up');
        $siblings = $item->menu->items()->get();
        $idx = $siblings->search(fn ($x) => $x->id === $item->id);
        $swap = $dir === 'up' ? $idx - 1 : $idx + 1;

        if ($swap >= 0 && $swap < $siblings->count()) {
            $other = $siblings[$swap];
            [$item->position, $other->position] = [$other->position, $item->position];
            $item->save();
            $other->save();
        }

        return redirect()->route('builder.menus.index');
    }

    public function removeItem(CmsMenuItem $item)
    {
        $item->delete();

        return redirect()->route('builder.menus.index')->with('status', 'Item removed.');
    }
}
