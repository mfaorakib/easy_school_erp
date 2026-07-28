<?php

namespace Modules\Communication\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['title', 'description', 'start_date', 'end_date', 'location', 'academic_year_id'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function scopeUpcoming($q)
    {
        return $q->whereDate('start_date', '>=', now()->toDateString())->orderBy('start_date');
    }
}
