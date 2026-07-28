<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A navigation menu bound to a location (header / footer). */
class CmsMenu extends Model
{
    use SoftDeletes;

    protected $fillable = ['location', 'title'];

    public function items(): HasMany
    {
        return $this->hasMany(CmsMenuItem::class, 'menu_id')->orderBy('position')->orderBy('id');
    }

    /** Top-level items (no parent) with their children eager-loaded. */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id')->with(['children.page', 'page']);
    }
}
