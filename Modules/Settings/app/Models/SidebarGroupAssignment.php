<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/** Overrides which section one sub-group currently belongs to — see the migration for the full explanation. */
class SidebarGroupAssignment extends Model
{
    protected $fillable = ['item_key', 'section_key'];
}
