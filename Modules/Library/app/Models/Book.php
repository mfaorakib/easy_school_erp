<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'author', 'publisher', 'isbn', 'book_category_id',
        'rack', 'quantity', 'available', 'price', 'is_active',
    ];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function scopeAvailable($q)
    {
        return $q->where('available', '>', 0);
    }
}
