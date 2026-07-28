<?php

namespace Modules\Fees\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fee_assignment_id', 'receipt_no', 'amount', 'fine', 'discount', 'refunded_amount', 'paid_on',
        'method', 'reference', 'note', 'received_by',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'fine'            => 'decimal:2',
        'discount'        => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'paid_on'         => 'date',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FeeAssignment::class, 'fee_assignment_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** How much of this payment (amount + fine) hasn't been refunded yet. */
    public function refundableAmount(): float
    {
        return max(0, round((float) $this->amount + (float) $this->fine - (float) $this->refunded_amount, 2));
    }

    public function isFullyRefunded(): bool
    {
        return $this->refundableAmount() <= 0;
    }
}
