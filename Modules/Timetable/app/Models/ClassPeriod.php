<?php

namespace Modules\Timetable\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A time slot in the daily schedule. Year-scoped. */
class ClassPeriod extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'start_time', 'end_time', 'is_break', 'position', 'is_active', 'academic_year_id'];

    protected $casts = ['is_break' => 'boolean', 'is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('position')->orderBy('start_time');
    }

    /** "09:00 – 09:45" for display. */
    public function timeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5).' – '.substr((string) $this->end_time, 0, 5);
    }
}
