<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A home hero/carousel slide. */
class Slider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'subtitle', 'image_path', 'link_url', 'link_label', 'position', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('position')->orderBy('id');
    }
}
