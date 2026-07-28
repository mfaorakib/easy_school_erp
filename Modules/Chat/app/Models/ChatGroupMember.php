<?php

namespace Modules\Chat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Membership of a user in a chat group. */
class ChatGroupMember extends Model
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';

    protected $fillable = ['group_id', 'user_id', 'role', 'last_read_at'];

    protected $casts = ['last_read_at' => 'datetime'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
