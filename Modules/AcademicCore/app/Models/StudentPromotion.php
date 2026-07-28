<?php

namespace Modules\AcademicCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Auditable promotion snapshot. Not year-scoped (spans two years). */
class StudentPromotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'previous_class_id', 'current_class_id',
        'previous_section_id', 'current_section_id',
        'previous_year_id', 'current_year_id',
        'admission_no', 'previous_roll_no', 'current_roll_no',
        'student_info', 'result_status', 'academic_year_id',
    ];

    protected $casts = ['student_info' => 'array'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
