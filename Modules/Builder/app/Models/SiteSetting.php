<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;

/** Single-row site/branding settings. Use SiteSetting::current(). */
class SiteSetting extends Model
{
    protected $guarded = ['id'];

    /** The one settings row, created with defaults on first access. */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
