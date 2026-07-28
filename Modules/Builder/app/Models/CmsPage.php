<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A public website page composed of ordered blocks. */
class CmsPage extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'is_home', 'is_published', 'meta_description'];

    protected $casts = [
        'is_home'      => 'boolean',
        'is_published' => 'boolean',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(CmsBlock::class, 'page_id')->orderBy('position')->orderBy('id');
    }

    public function visibleBlocks(): HasMany
    {
        return $this->blocks()->where('is_visible', true);
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }
}
