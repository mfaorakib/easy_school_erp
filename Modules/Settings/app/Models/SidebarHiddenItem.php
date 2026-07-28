<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarHiddenItem extends Model
{
    protected $fillable = ['role', 'item_key'];
}
