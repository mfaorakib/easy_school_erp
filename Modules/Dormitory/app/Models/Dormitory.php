<?php

namespace Modules\Dormitory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dormitory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'address', 'note', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function rooms(): HasMany
    {
        return $this->hasMany(DormitoryRoom::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
