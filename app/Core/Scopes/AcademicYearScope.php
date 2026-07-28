<?php

namespace App\Core\Scopes;

use App\Core\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Auto-filters a model to the active academic year (the reference system's
 * StatusAcademicSchoolScope, minus the multi-tenant school_id).
 *
 * If no active year is resolvable (e.g. during seeding) the scope is a no-op,
 * so it never breaks migrations or global lookups.
 */
class AcademicYearScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $yearId = Context::academicYearId();

        if ($yearId !== null) {
            $builder->where($model->getTable().'.academic_year_id', $yearId);
        }
    }
}
