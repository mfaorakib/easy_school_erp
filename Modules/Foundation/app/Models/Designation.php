<?php

namespace Modules\Foundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
