<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;

class FeeMaster extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'fee_group_id', 'fee_type_id', 'class_id', 'amount', 'due_date',
        'fine_type', 'fine_value', 'academic_year_id',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'fine_value' => 'decimal:2',
        'due_date'   => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(FeeGroup::class, 'fee_group_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FeeAssignment::class);
    }

    /** Fine due on a given date if unpaid and past due (0 if not overdue). */
    public function fineFor(\DateTimeInterface $on): float
    {
        if ($this->fine_type === 'none' || ! $this->due_date || $on <= $this->due_date) {
            return 0.0;
        }

        return $this->fine_type === 'percentage'
            ? round((float) $this->amount * (float) $this->fine_value / 100, 2)
            : (float) $this->fine_value;
    }
}
