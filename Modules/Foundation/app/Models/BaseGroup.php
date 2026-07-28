<?php

namespace Modules\Foundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function setups(): HasMany
    {
        return $this->hasMany(BaseSetup::class);
    }
}
