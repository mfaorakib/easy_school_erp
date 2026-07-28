<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'item_category_id', 'unit', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function receives(): HasMany
    {
        return $this->hasMany(ItemReceive::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ItemIssue::class);
    }

    public function sells(): HasMany
    {
        return $this->hasMany(ItemSell::class);
    }

    /** Derived stock = received (net of supplier returns) − issued-and-not-returned − sold (net of buyer returns). */
    public function getStockAttribute(): int
    {
        $received = (int) $this->receives()->sum('quantity') - (int) $this->receives()->sum('returned_quantity');
        $issued   = (int) $this->issues()->where('is_returned', false)->sum('quantity');
        $sold     = (int) $this->sells()->sum('quantity') - (int) $this->sells()->sum('returned_quantity');

        return $received - $issued - $sold;
    }
}
