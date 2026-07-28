<?php

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportRoute extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'fare', 'start_point', 'end_point', 'note', 'is_active'];

    protected $casts = ['fare' => 'decimal:2', 'is_active' => 'boolean'];

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'route_vehicle');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
