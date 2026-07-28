<?php

namespace Modules\Payroll\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A named salary grade: basic pay + earning/deduction components. Year-scoped. */
class SalaryTemplate extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'basic_salary', 'is_active', 'academic_year_id'];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    public function components(): HasMany
    {
        return $this->hasMany(SalaryComponent::class)->orderBy('position')->orderBy('id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
