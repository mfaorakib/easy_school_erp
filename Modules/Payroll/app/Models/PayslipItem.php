<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A resolved earning/deduction line captured on a payslip. */
class PayslipItem extends Model
{
    protected $fillable = ['payslip_id', 'name', 'type', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }
}
