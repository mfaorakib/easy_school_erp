<?php

namespace Modules\Leave\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A work shift. Year-scoped. */
class Shift extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'start_time', 'end_time', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function timeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5).' – '.substr((string) $this->end_time, 0, 5);
    }
}
