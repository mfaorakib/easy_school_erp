<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\SidebarPosition;

/**
 * Resolves and persists the admin-customized DISPLAY ORDER of sidebar
 * sections/sub-groups. Never touches routes, permissions, or links — only
 * decides what order a caller's own already-built list should be presented
 * in. If a key was removed from the code (a module deleted) its stored
 * position is simply ignored; if a NEW key appears that was never
 * positioned before, it's appended at the end in its original relative
 * order — so nothing can ever "disappear" from the sidebar just because
 * this table is empty, stale, or partially configured.
 */
class SidebarPositionService
{
    /**
     * Reorder $defaultKeys according to any saved positions under $parentKey
     * ('' = top-level sections; a section's own key = that section's
     * sub-groups). Keys with no saved position keep their original relative
     * order and are appended after the ones that do have a saved position.
     *
     * @param  string[]  $defaultKeys
     * @return string[]
     */
    public function orderedKeys(string $parentKey, array $defaultKeys): array
    {
        $stored = SidebarPosition::where('parent_key', $parentKey)
            ->orderBy('position')
            ->pluck('item_key')
            ->all();

        $known = array_values(array_intersect($stored, $defaultKeys));
        $missing = array_values(array_filter($defaultKeys, fn ($k) => ! in_array($k, $known, true)));

        return array_merge($known, $missing);
    }

    /**
     * Persist a new order for $parentKey. Only keys present in
     * $allowedKeys are ever written (defence in depth against a tampered
     * request submitting an unknown key) — anything else in $orderedKeys
     * is silently dropped, not stored, not erroring.
     *
     * @param  string[]  $orderedKeys
     * @param  string[]  $allowedKeys
     */
    public function save(string $parentKey, array $orderedKeys, array $allowedKeys): void
    {
        $safeKeys = array_values(array_intersect($orderedKeys, $allowedKeys));

        SidebarPosition::where('parent_key', $parentKey)->delete();

        foreach ($safeKeys as $i => $key) {
            SidebarPosition::create([
                'parent_key' => $parentKey,
                'item_key'   => $key,
                'position'   => $i,
            ]);
        }
    }

    /** Clear any custom order for $parentKey, reverting to the code's built-in default order. */
    public function reset(string $parentKey): void
    {
        SidebarPosition::where('parent_key', $parentKey)->delete();
    }
}
