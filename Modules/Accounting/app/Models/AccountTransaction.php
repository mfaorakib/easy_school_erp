<?php

namespace Modules\Accounting\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A single income or expense entry. Year-scoped. */
class AccountTransaction extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'type', 'account_head_id', 'bank_account_id', 'title', 'amount',
        'transaction_date', 'payment_method', 'reference', 'note',
        'attachment_path', 'created_by', 'academic_year_id',
        'source_module', 'source_id',
        'parent_transaction_id', 'is_adjustment', 'adjustment_reason',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date',
        'is_adjustment'    => 'boolean',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class, 'account_head_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The original transaction this one corrects, if this is an adjustment. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_transaction_id');
    }

    /** Corrections posted against this transaction (partial returns, refunds, ...). */
    public function adjustments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_transaction_id');
    }

    public function scopeType($q, string $type)
    {
        return $q->where('type', $type);
    }

    /**
     * Was this entry system-managed rather than hand-entered? True for both
     * module-posted entries (Fees/Payroll/Inventory, which carry a
     * source_module) AND refund/return adjustments (which link back via
     * parent_transaction_id and carry no source_module). Both must be locked
     * from manual edit/delete in the Accounting UI, or a correction could be
     * silently altered/removed and desynced from the module that owns it.
     */
    public function isAutoPosted(): bool
    {
        return $this->source_module !== null || $this->is_adjustment;
    }
}
