<?php

namespace Modules\Foundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseSetup extends Model
{
    use SoftDeletes;

    protected $fillable = ['base_group_id', 'name', 'is_active', 'position'];

    protected $casts = ['is_active' => 'boolean'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(BaseGroup::class, 'base_group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
