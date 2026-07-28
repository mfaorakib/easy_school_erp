<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A public submission from a Contact or Newsletter section. */
class Message extends Model
{
    use SoftDeletes;

    protected $table = 'cms_messages';

    protected $fillable = ['type', 'name', 'email', 'phone', 'subject', 'body', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function scopeUnread($q)
    {
        return $q->where('is_read', false);
    }
}
