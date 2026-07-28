<?php

namespace Modules\Foundation\Services;

use App\Core\Support\Context;
use Illuminate\Support\Facades\DB;
use Modules\Foundation\Models\AcademicYear;

/**
 * Academic-year lifecycle (the reference system SmAcademicYearController logic, cleaned up):
 * create → activate → clone structure into the new year.
 */
class AcademicYearService
{
    /** Active year id, resolved once per request. */
    public function activeId(): ?int
    {
        return Context::academicYearId();
    }

    public function active(): ?AcademicYear
    {
        return AcademicYear::current();
    }

    /**
     * Create a year and (optionally) activate it. When $cloneFromId is given,
     * structural data is copied into the new year by registered cloners.
     */
    public function create(array $data, bool $activate = true, ?int $cloneFromId = null): AcademicYear
    {
        return DB::transaction(function () use ($data, $activate, $cloneFromId) {
            $year = AcademicYear::create([
                'title'       => $data['title'],
                'year'        => $data['year'],
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'template_id' => $cloneFromId,
                'is_active'   => false,
            ]);

            if ($cloneFromId) {
                $this->cloneStructure($cloneFromId, $year->id);
            }

            if ($activate) {
                $this->activate($year->id);
            }

            return $year->refresh();
        });
    }

    /** Flip the single active year (the reference system sessionChange). */
    public function activate(int $yearId): void
    {
        DB::transaction(function () use ($yearId) {
            AcademicYear::query()->update(['is_active' => false]);
            AcademicYear::whereKey($yearId)->update(['is_active' => true]);
        });

        Context::setAcademicYearId($yearId);
    }

    /**
     * Copy year-scoped structural rows from one year to another.
     * Cloners are registered per module (classes, sections, subjects, grades…)
     * as they are built; Foundation ships the empty registry.
     *
     * @var array<int,callable(int $from,int $to):void>
     */
    protected static array $cloners = [];

    public static function registerCloner(callable $cloner): void
    {
        static::$cloners[] = $cloner;
    }

    protected function cloneStructure(int $fromYearId, int $toYearId): void
    {
        foreach (static::$cloners as $cloner) {
            $cloner($fromYearId, $toYearId);
        }
    }
}
