<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\HumanResource\Models\Staff;

class SalaryAdvance extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'staff_id', 'amount', 'reason', 'status', 'recovered_amount',
        'requested_at', 'review_note', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'recovered_amount' => 'decimal:2',
        'requested_at'     => 'date',
        'reviewed_at'      => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function outstandingAmount(): float
    {
        return max(0.0, round((float) $this->amount - (float) $this->recovered_amount, 2));
    }

    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    /** Approved but not yet fully recovered. */
    public function scopeOutstanding($q)
    {
        return $q->where('status', self::STATUS_APPROVED)->whereColumn('recovered_amount', '<', 'amount');
    }
}
