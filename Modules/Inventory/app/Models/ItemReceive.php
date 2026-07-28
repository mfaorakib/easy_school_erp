<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemReceive extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'item_store_id', 'supplier_id', 'quantity', 'returned_quantity',
        'unit_price', 'received_on', 'note', 'received_by', 'voucher_no',
    ];

    protected $casts = ['received_on' => 'date', 'unit_price' => 'decimal:2'];

    /** Net quantity still held after any returns to the supplier. */
    public function getNetQuantityAttribute(): int
    {
        return (int) $this->quantity - (int) $this->returned_quantity;
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(ItemStore::class, 'item_store_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
