<?php

namespace App\Core\Concerns;

use App\Core\Scopes\AcademicYearScope;
use App\Core\Support\Context;
use Modules\Foundation\Models\AcademicYear;

/**
 * Apply to any operational model that lives inside an academic year.
 * - adds the global AcademicYearScope (reads are filtered to the active year)
 * - stamps academic_year_id on create when not set
 *
 * Requires an `academic_year_id` column on the table.
 */
trait BelongsToAcademicYear
{
    public static function bootBelongsToAcademicYear(): void
    {
        static::addGlobalScope(new AcademicYearScope);

        static::creating(function ($model) {
            if (empty($model->academic_year_id)) {
                $model->academic_year_id = Context::academicYearId();
            }
        });
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** Read across all years (bypass the active-year filter). */
    public function scopeAllYears($query)
    {
        return $query->withoutGlobalScope(AcademicYearScope::class);
    }
}
