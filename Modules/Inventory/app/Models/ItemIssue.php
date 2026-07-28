<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemIssue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'item_store_id', 'issued_to', 'quantity',
        'issue_date', 'return_date', 'is_returned', 'note', 'issued_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'return_date' => 'date',
        'is_returned' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }
}
