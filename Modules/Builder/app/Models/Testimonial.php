<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A testimonial / review shown on the public site. */
class Testimonial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'designation', 'organization', 'photo_path', 'quote', 'rating', 'position', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('position')->orderBy('id');
    }
}
