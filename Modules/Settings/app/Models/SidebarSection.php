<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/** A custom umbrella section an admin created — see the migration for why the 10 built-in ones never live here. */
class SidebarSection extends Model
{
    protected $fillable = ['key', 'label', 'icon'];

    /** The view only ever has this section's `key` (never its numeric id — see SidebarNav::customSections()), so route-model-binding must resolve on that. */
    public function getRouteKeyName(): string
    {
        return 'key';
    }
}
