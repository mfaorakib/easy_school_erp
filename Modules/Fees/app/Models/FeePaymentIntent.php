<?php

namespace Modules\Fees\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Settings\Models\PaymentMethod;

/** A tracked online-checkout attempt for one fee assignment. See migration comment for the full flow. */
class FeePaymentIntent extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'token', 'fee_assignment_id', 'payment_method_id', 'amount', 'initiated_by',
        'status', 'gateway_reference', 'gateway_payload', 'failure_reason', 'fee_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FeeAssignment::class, 'fee_assignment_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
