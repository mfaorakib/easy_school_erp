<?php

namespace Modules\Accounting\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An income or expense category. Year-scoped. */
class AccountHead extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    protected $fillable = ['name', 'type', 'description', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeIncome($q)
    {
        return $q->where('type', self::TYPE_INCOME);
    }

    public function scopeExpense($q)
    {
        return $q->where('type', self::TYPE_EXPENSE);
    }
}
