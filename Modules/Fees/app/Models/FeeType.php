<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeType extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'code', 'description', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];
}
