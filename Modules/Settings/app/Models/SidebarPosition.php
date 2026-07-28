<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarPosition extends Model
{
    protected $fillable = ['parent_key', 'item_key', 'position'];
}
