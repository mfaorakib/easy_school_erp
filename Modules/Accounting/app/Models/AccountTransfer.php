<?php

namespace Modules\Accounting\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A money transfer between two bank accounts. Year-scoped. */
class AccountTransfer extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'from_bank_account_id', 'to_bank_account_id', 'amount',
        'purpose', 'transfer_date', 'created_by', 'academic_year_id',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_bank_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_account_id');
    }
}
