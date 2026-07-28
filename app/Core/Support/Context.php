<?php

namespace App\Core\Support;

use Illuminate\Support\Facades\Schema;
use Modules\Foundation\Models\AcademicYear;

/**
 * Resolves the "active academic year" for the current request.
 *
 * Mirrors the reference system's getAcademicId()/YearCheck behaviour: nearly every
 * operational record is scoped to the active academic year. Here it is a
 * single, cached, testable entry point instead of a global helper.
 */
class Context
{
    protected static ?int $academicYearId = null;

    public static function academicYearId(): ?int
    {
        if (static::$academicYearId !== null) {
            return static::$academicYearId;
        }

        // Explicit request/session override (year switcher).
        if (function_exists('session') && session()->has('academic_year_id')) {
            return static::$academicYearId = (int) session('academic_year_id');
        }

        // Fall back to the flagged-active year. Guard against boot/migration
        // time when the table may not exist yet.
        try {
            if (! Schema::hasTable('academic_years')) {
                return null;
            }
            $id = AcademicYear::query()->where('is_active', true)->value('id');
            return static::$academicYearId = ($id ? (int) $id : null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function setAcademicYearId(?int $id): void
    {
        static::$academicYearId = $id;
        if (function_exists('session')) {
            $id === null ? session()->forget('academic_year_id')
                         : session(['academic_year_id' => $id]);
        }
    }

    public static function flush(): void
    {
        static::$academicYearId = null;
    }
}
