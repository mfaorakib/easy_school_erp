<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\CmsBlock;
use Modules\Builder\Models\CmsPage;
use Modules\Builder\Services\BuilderService;
use Modules\Builder\Support\BlockType;

class BlockController extends Controller
{
    public function index(CmsPage $page)
    {
        $page->load('blocks');
        $types = BlockType::grouped();

        return view('builder::blocks.index', compact('page', 'types'));
    }

    public function store(Request $request, CmsPage $page, BuilderService $builder)
    {
        $request->validate(['type' => ['required', 'string']]);

        if (! BlockType::exists($request->type)) {
            return redirect()->route('builder.blocks.index', $page)->with('status', 'Unknown block type.');
        }

        $block = $builder->addBlock($page, $request->type);

        return redirect()->route('builder.blocks.index', ['page' => $page->id, 'block' => $block->id])
            ->with('status', 'Section added.');
    }

    public function update(Request $request, CmsBlock $block, BuilderService $builder)
    {
        $data = $request->input('data', []);

        // Collapse repeater rows that were left entirely blank.
        foreach (BlockType::fields($block->type) as $field) {
            if (($field['type'] ?? null) === 'repeater') {
                $key = $field['key'];
                $data[$key] = array_values(array_filter(
                    $data[$key] ?? [],
                    fn ($row) => collect($row)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty()
                ));
            }
        }

        $settings = $request->input('settings', []);

        $builder->updateBlock($block, $data, $request->boolean('is_visible'), $settings);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('builder.blocks.index', ['page' => $block->page_id, 'block' => $block->id])
            ->with('status', 'Section saved.');
    }

    public function duplicate(CmsBlock $block, BuilderService $builder)
    {
        $copy = $builder->duplicateBlock($block);

        return redirect()->route('builder.blocks.index', ['page' => $block->page_id, 'block' => $copy->id])
            ->with('status', 'Section duplicated.');
    }

    /** Persist a new order from the drag-and-drop list. AJAX. */
    public function reorder(Request $request, CmsPage $page, BuilderService $builder)
    {
        $ids = $request->input('order', []);
        $builder->reorder($page, is_array($ids) ? $ids : []);

        return response()->json(['ok' => true]);
    }

    public function move(Request $request, CmsBlock $block, BuilderService $builder)
    {
        $builder->moveBlock($block, $request->input('direction', 'up'));

        return redirect()->route('builder.blocks.index', $block->page);
    }

    public function destroy(CmsBlock $block)
    {
        $page = $block->page;
        $block->delete();

        return redirect()->route('builder.blocks.index', $page)->with('status', 'Section removed.');
    }
}
