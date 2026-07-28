<?php

namespace Modules\Dashboard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A personal dashboard to-do item. */
class Todo extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'title', 'is_done', 'position'];

    protected $casts = ['is_done' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId)->orderBy('is_done')->orderBy('position')->orderByDesc('id');
    }
}
