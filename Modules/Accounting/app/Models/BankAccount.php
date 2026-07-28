<?php

namespace Modules\Accounting\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A bank/cash account. Current balance is derived, never stored. Year-scoped. */
class BankAccount extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'bank_name', 'account_name', 'account_number', 'account_type',
        'opening_balance', 'note', 'is_active', 'academic_year_id',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** A readable label for selects. */
    public function label(): string
    {
        return trim($this->bank_name.($this->account_number ? ' — '.$this->account_number : ''));
    }
}
