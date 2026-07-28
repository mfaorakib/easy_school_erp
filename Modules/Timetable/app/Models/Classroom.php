<?php

namespace Modules\Timetable\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A physical room. Year-scoped. */
class Classroom extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['room_no', 'capacity', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
