<?php

namespace Modules\AcademicCore\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One enrollment of a student in a class/section for a year. Year-scoped. */
class StudentRecord extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'student_id', 'class_id', 'section_id', 'academic_year_id',
        'roll_no', 'is_default', 'is_promote', 'is_graduate',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'is_promote'  => 'boolean',
        'is_graduate' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function scopeLive($q)
    {
        return $q->where('is_promote', false);
    }
}
