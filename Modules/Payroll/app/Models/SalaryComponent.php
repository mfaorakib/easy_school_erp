<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An earning or deduction line of a salary template. */
class SalaryComponent extends Model
{
    use SoftDeletes;

    public const TYPE_EARNING = 'earning';
    public const TYPE_DEDUCTION = 'deduction';

    protected $fillable = ['salary_template_id', 'name', 'type', 'calc_type', 'value', 'position'];

    protected $casts = ['value' => 'decimal:2'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SalaryTemplate::class, 'salary_template_id');
    }

    /** Resolve this component to a money amount for a given basic salary. */
    public function resolve(float $basic): float
    {
        return $this->calc_type === 'percent'
            ? round($basic * (float) $this->value / 100, 2)
            : round((float) $this->value, 2);
    }
}
