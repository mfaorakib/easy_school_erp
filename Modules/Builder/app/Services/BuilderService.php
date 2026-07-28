<?php

namespace Modules\Builder\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Builder\Models\CmsBlock;
use Modules\Builder\Models\CmsMenu;
use Modules\Builder\Models\CmsPage;
use Modules\Builder\Models\SiteSetting;
use Modules\Builder\Models\Slider;
use Modules\Builder\Models\Testimonial;
use Modules\Builder\Support\BlockType;

/**
 * The frontend-from-backend engine: resolves the public site's settings, pages,
 * blocks and menus for rendering, and manages page blocks for the admin builder.
 */
class BuilderService
{
    public function settings(): SiteSetting
    {
        return SiteSetting::current();
    }

    /** Resolve a media value: an absolute URL is used as-is, else it's a public-disk path. */
    public static function media(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($value, ['http://', 'https://', '/'])
            ? $value
            : asset('storage/'.$value);
    }

    /** The published home page (with visible blocks), or null. */
    public function homePage(): ?CmsPage
    {
        return CmsPage::published()->where('is_home', true)->with('visibleBlocks')->first();
    }

    /** A published page by slug (with visible blocks), or null. */
    public function pageBySlug(string $slug): ?CmsPage
    {
        return CmsPage::published()->where('slug', $slug)->with('visibleBlocks')->first();
    }

    /** The root items of a menu location (header/footer) with children, or empty. */
    public function menu(string $location): Collection
    {
        $menu = CmsMenu::where('location', $location)->first();

        return $menu ? $menu->rootItems()->get() : collect();
    }

    public function activeSliders(): Collection
    {
        return Slider::active()->get();
    }

    public function activeTestimonials(): Collection
    {
        return Testimonial::active()->get();
    }

    // ------------------------------------------------------------ Block editing

    /** Append a block of a given type to a page with sane empty data. */
    public function addBlock(CmsPage $page, string $type): CmsBlock
    {
        abort_unless(BlockType::exists($type), 422, 'Unknown block type.');

        $position = (int) $page->blocks()->max('position') + 1;

        return $page->blocks()->create([
            'type'       => $type,
            'position'   => $position,
            'data'       => \Modules\Builder\Support\BlockStarter::data($type),
            'settings'   => \Modules\Builder\Support\BlockStarter::settings($type),
            'is_visible' => true,
        ]);
    }

    public function updateBlock(CmsBlock $block, array $data, bool $isVisible = true, ?array $settings = null): CmsBlock
    {
        $attrs = ['data' => $data, 'is_visible' => $isVisible];

        if ($settings !== null) {
            $attrs['settings'] = $settings;
        }

        $block->update($attrs);

        return $block;
    }

    /** Clone a block (content + style) and drop it right after the original. */
    public function duplicateBlock(CmsBlock $block): CmsBlock
    {
        $copy = $block->replicate(['created_at', 'updated_at']);
        $copy->position = $block->position + 1;

        // Push everything after the original down one slot to make room.
        $block->page->blocks()
            ->where('position', '>', $block->position)
            ->increment('position');

        $copy->save();

        return $copy;
    }

    /** Persist a new order given an array of block ids in display order. */
    public function reorder(CmsPage $page, array $orderedIds): void
    {
        $position = 1;
        foreach ($orderedIds as $id) {
            $page->blocks()->whereKey($id)->update(['position' => $position++]);
        }
    }

    /** Move a block up or down among its page's blocks. */
    public function moveBlock(CmsBlock $block, string $direction): void
    {
        $ordered = $block->page->blocks()->get();
        $index = $ordered->search(fn ($b) => $b->id === $block->id);
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= $ordered->count()) {
            return;
        }

        $other = $ordered[$swapWith];
        [$block->position, $other->position] = [$other->position, $block->position];
        $block->save();
        $other->save();
    }

    /** Ensure a URL-safe unique slug for a page title. */
    public function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $i = 2;

        while (CmsPage::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Enforce a single home page: unset is_home on all others. */
    public function markHome(CmsPage $page): void
    {
        CmsPage::where('id', '!=', $page->id)->where('is_home', true)->update(['is_home' => false]);
        $page->update(['is_home' => true]);
    }
}
