<?php

namespace Modules\Foundation\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['module', 'label', 'enabled'];

    protected $casts = ['enabled' => 'boolean'];
}
