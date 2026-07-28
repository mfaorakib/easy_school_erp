<?php

namespace Modules\Behaviour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BehaviourType extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'point', 'is_active'];

    protected $casts = ['point' => 'decimal:2', 'is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
