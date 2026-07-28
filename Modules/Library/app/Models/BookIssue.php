<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookIssue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_id', 'library_member_id', 'issue_date', 'due_date',
        'return_date', 'fine', 'is_returned', 'issued_by',
        'fine_collected', 'fine_collected_at',
    ];

    protected $casts = [
        'issue_date'         => 'date',
        'due_date'           => 'date',
        'return_date'        => 'date',
        'fine'               => 'decimal:2',
        'is_returned'        => 'boolean',
        'fine_collected'     => 'boolean',
        'fine_collected_at'  => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(LibraryMember::class, 'library_member_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'issued_by');
    }
}
