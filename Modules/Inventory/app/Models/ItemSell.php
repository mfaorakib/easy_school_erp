<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemSell extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'item_store_id', 'sold_to', 'buyer_name', 'buyer_contact',
        'quantity', 'returned_quantity', 'unit_price', 'sold_on', 'note', 'sold_by', 'voucher_no',
    ];

    protected $casts = ['sold_on' => 'date', 'unit_price' => 'decimal:2'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function soldTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to');
    }

    /** However the buyer was recorded — a system user, or a free-text walk-in name. */
    public function buyerLabel(): string
    {
        return $this->soldTo?->name ?? $this->buyer_name ?? '—';
    }

    /** Net quantity still with the buyer after any returns. */
    public function getNetQuantityAttribute(): int
    {
        return (int) $this->quantity - (int) $this->returned_quantity;
    }
}
