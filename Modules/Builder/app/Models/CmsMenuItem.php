<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A single menu entry. Resolves to a CMS page or an explicit url. */
class CmsMenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['menu_id', 'label', 'url', 'page_id', 'parent_id', 'position', 'open_new_tab'];

    protected $casts = ['open_new_tab' => 'boolean'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'menu_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /** The resolved href: linked page's public URL, else the raw url, else '#'. */
    public function href(): string
    {
        if ($this->page) {
            return $this->page->is_home ? url('/') : url('/p/'.$this->page->slug);
        }

        return $this->url ?: '#';
    }
}
