<?php

namespace Modules\Chat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A direct 1-to-1 message. Not year-scoped. */
class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id', 'recipient_id', 'body', 'message_type',
        'attachment_path', 'attachment_name', 'reply_to_id', 'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    /** Messages exchanged between two users (either direction). */
    public function scopeBetween($q, int $a, int $b)
    {
        return $q->where(function ($w) use ($a, $b) {
            $w->where('sender_id', $a)->where('recipient_id', $b);
        })->orWhere(function ($w) use ($a, $b) {
            $w->where('sender_id', $b)->where('recipient_id', $a);
        });
    }
}
