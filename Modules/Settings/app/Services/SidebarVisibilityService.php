<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\SidebarHiddenItem;

/**
 * Per-role sidebar visibility toggle. A hidden (role, item_key) pair simply
 * means SidebarNav skips that section/group/link when rendering the real
 * sidebar for a user holding that role — never touches routes/permissions,
 * only what's shown. Only Super Admin can set this, and Super Admin's own
 * sidebar is never filtered by it (see SidebarNav::isHiddenForCurrentUser()).
 */
class SidebarVisibilityService
{
    public function isHidden(string $role, string $itemKey): bool
    {
        return SidebarHiddenItem::where('role', $role)->where('item_key', $itemKey)->exists();
    }

    /** @return string[] */
    public function hiddenKeysFor(string $role): array
    {
        return SidebarHiddenItem::where('role', $role)->pluck('item_key')->all();
    }

    /** Flip hidden state for one item; returns the NEW state (true = now hidden). */
    public function toggle(string $role, string $itemKey): bool
    {
        $existing = SidebarHiddenItem::where('role', $role)->where('item_key', $itemKey)->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        SidebarHiddenItem::create(['role' => $role, 'item_key' => $itemKey]);

        return true;
    }
}
