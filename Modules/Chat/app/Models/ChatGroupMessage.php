<?php

namespace Modules\Chat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A message posted to a group. */
class ChatGroupMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_id', 'sender_id', 'body', 'message_type',
        'attachment_path', 'attachment_name',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class, 'group_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
