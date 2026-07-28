<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\SidebarNav;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Access\Enums\Role;
use Modules\Settings\Models\SidebarGroupAssignment;
use Modules\Settings\Models\SidebarSection;
use Modules\Settings\Services\SidebarPositionService;
use Modules\Settings\Services\SidebarVisibilityService;

class SidebarManagerController extends Controller
{
    public function __construct(
        protected SidebarNav $nav,
        protected SidebarPositionService $positions,
        protected SidebarVisibilityService $visibility,
    ) {
    }

    public function edit()
    {
        $sections = $this->nav->orderedSections();
        $groupLabels = $this->nav->groups(); // ['key' => ['label' => ..., ...], ...] — use $groupLabels[$key]['label'] to display a sub-group's name
        $sectionKeys = array_column($sections, 'key');

        // For each section, compute the customized sub-group order to actually display.
        $sections = array_map(function ($section) {
            $section['orderedGroups'] = $this->positions->orderedKeys($section['key'], $section['groups']);
            return $section;
        }, $sections);

        $orderedLinksByGroup = [];
        foreach ($groupLabels as $key => $group) {
            $orderedLinksByGroup[$key] = $this->nav->orderedLinks($key, $group['links']);
        }

        $hiddenKeys = $this->visibility->hiddenKeysFor(Role::Admin->value);

        return view('settings::sidebar-manager.edit', compact('sections', 'groupLabels', 'sectionKeys', 'orderedLinksByGroup', 'hiddenKeys'));
    }

    public function updateSections(Request $request)
    {
        $order = $request->input('order', []); // array of section keys, new order, from a hidden input
        $allowed = array_column($this->nav->defaultSections(), 'key');
        $this->positions->save('', is_array($order) ? $order : [], $allowed);

        return back()->with('status', __('ui.order_saved'));
    }

    public function updateGroups(Request $request)
    {
        $sectionKey = (string) $request->input('section_key', '');
        $order = $request->input('order', []);
        $section = collect($this->nav->defaultSections())->firstWhere('key', $sectionKey);
        abort_if(! $section, 404);
        $this->positions->save($sectionKey, is_array($order) ? $order : [], $section['groups']);

        return back()->with('status', __('ui.order_saved'));
    }

    public function reset(Request $request)
    {
        $scope = (string) $request->input('scope', ''); // '' = reset top-level sections; otherwise a section key = reset that section's sub-groups
        $this->positions->reset($scope);

        return back()->with('status', __('ui.order_reset'));
    }

    /** Create a new custom umbrella section — starts empty until sub-groups are moved into it. */
    public function storeSection(Request $request)
    {
        $request->validate(['label' => ['required', 'string', 'max:100']]);

        $builtInKeys = array_column($this->nav->defaultSections(), 'key');
        $base = Str::slug($request->string('label'), '_') ?: 'section';
        $key = $base;
        $suffix = 1;
        while (in_array($key, $builtInKeys, true) || SidebarSection::where('key', $key)->exists()) {
            $key = $base.'_'.(++$suffix);
        }

        SidebarSection::create([
            'key'   => $key,
            'label' => $request->string('label'),
            'icon'  => $request->input('icon') ?: '📁',
        ]);

        return back()->with('status', __('ui.order_saved'));
    }

    /** Delete a custom section — any sub-groups currently assigned to it automatically fall back to their default section (they're never lost or hidden). */
    public function destroySection(SidebarSection $section)
    {
        SidebarGroupAssignment::where('section_key', $section->key)->delete();
        $this->positions->reset($section->key);
        $section->delete();

        return back()->with('status', __('ui.order_saved'));
    }

    /** Move one sub-group to a (possibly different) section. */
    public function assignGroup(Request $request)
    {
        $request->validate([
            'item_key'    => ['required', 'string'],
            'section_key' => ['required', 'string'],
        ]);

        $itemKey = $request->string('item_key')->toString();
        $sectionKey = $request->string('section_key')->toString();

        $knownItemKeys = array_keys($this->nav->groups());
        $knownSectionKeys = array_column($this->nav->allSections(), 'key');

        abort_unless(in_array($itemKey, $knownItemKeys, true), 422, 'Unknown sub-group.');
        abort_unless(in_array($sectionKey, $knownSectionKeys, true), 422, 'Unknown section.');

        SidebarGroupAssignment::updateOrCreate(['item_key' => $itemKey], ['section_key' => $sectionKey]);

        return back()->with('status', __('ui.order_saved'));
    }

    /** Reorder one sub-group's own links. */
    public function updateLinks(Request $request)
    {
        $groupKey = (string) $request->input('group_key', '');
        $order = $request->input('order', []);
        $groups = $this->nav->groups();
        abort_if(! isset($groups[$groupKey]), 404);
        $allowedSlugs = array_column($groups[$groupKey]['links'], 0);
        $this->positions->save($groupKey, is_array($order) ? $order : [], $allowedSlugs);

        return back()->with('status', __('ui.order_saved'));
    }

    /** Toggle whether a section/group/link is hidden from the Admin role. */
    public function toggleHidden(Request $request)
    {
        $request->validate(['item_key' => ['required', 'string']]);
        $itemKey = $request->string('item_key')->toString();

        $knownSectionKeys = array_column($this->nav->allSections(), 'key');
        $knownGroupKeys = array_keys($this->nav->groups());
        $knownLinkSlugs = collect($this->nav->groups())->flatMap(fn ($g) => array_column($g['links'], 0))->unique()->all();
        $known = array_merge($knownSectionKeys, $knownGroupKeys, $knownLinkSlugs);
        abort_unless(in_array($itemKey, $known, true), 422, 'Unknown sidebar item.');

        $this->visibility->toggle(Role::Admin->value, $itemKey);

        return back()->with('status', __('ui.order_saved'));
    }
}
