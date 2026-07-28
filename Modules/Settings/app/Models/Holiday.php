<?php

namespace Modules\Settings\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A school holiday (single day or range). Year-scoped. */
class Holiday extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['title', 'from_date', 'to_date', 'description', 'academic_year_id'];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    public function days(): int
    {
        return $this->from_date->diffInDays($this->to_date) + 1;
    }
}
