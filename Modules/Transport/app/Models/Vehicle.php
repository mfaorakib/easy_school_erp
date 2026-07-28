<?php

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_no', 'model', 'driver_name', 'driver_phone',
        'driver_license', 'capacity', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(TransportRoute::class, 'route_vehicle');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
