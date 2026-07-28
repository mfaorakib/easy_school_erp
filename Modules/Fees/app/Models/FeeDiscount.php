<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeDiscount extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'code', 'amount_type', 'amount', 'is_active', 'academic_year_id'];

    protected $casts = ['amount' => 'decimal:2', 'is_active' => 'boolean'];

    /** Resolve this discount against a base amount. */
    public function resolve(float $base): float
    {
        return $this->amount_type === 'percentage'
            ? round($base * ((float) $this->amount) / 100, 2)
            : (float) $this->amount;
    }
}
