<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamType extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];
}
