<?php

namespace Modules\Payroll\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounting\Models\BankAccount;
use Modules\HumanResource\Models\Staff;

/** A generated payslip for one staff member for one month. Year-scoped. */
class Payslip extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const STATUS_GENERATED = 'generated';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'staff_id', 'salary_template_id', 'period', 'basic_salary',
        'gross_earnings', 'total_deductions', 'net_pay', 'advance_deduction', 'status',
        'payment_method', 'bank_account_id', 'paid_on', 'note', 'generated_by', 'academic_year_id',
    ];

    protected $casts = [
        'basic_salary'      => 'decimal:2',
        'gross_earnings'    => 'decimal:2',
        'total_deductions'  => 'decimal:2',
        'net_pay'           => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'paid_on'           => 'date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /** What actually gets paid out this time — net pay minus any advance being recovered. */
    public function payableAmount(): float
    {
        return round((float) $this->net_pay - (float) $this->advance_deduction, 2);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }

    public function earnings(): HasMany
    {
        return $this->items()->where('type', 'earning');
    }

    public function deductions(): HasMany
    {
        return $this->items()->where('type', 'deduction');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function scopePeriod($q, string $period)
    {
        return $q->where('period', $period);
    }
}
