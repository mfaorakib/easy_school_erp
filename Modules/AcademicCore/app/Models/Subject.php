<?php

namespace Modules\AcademicCore\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'code', 'type', 'pass_mark', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean', 'pass_mark' => 'float'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
