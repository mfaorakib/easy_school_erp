<?php

namespace Modules\Leave\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A leave category with an annual day quota. Year-scoped. */
class LeaveType extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'days_allowed', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
