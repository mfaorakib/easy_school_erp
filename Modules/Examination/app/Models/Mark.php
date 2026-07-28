<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\Subject;

class Mark extends Model
{
    use BelongsToAcademicYear;

    protected $fillable = [
        'exam_id', 'student_id', 'subject_id', 'class_id', 'section_id',
        'marks_obtained', 'is_absent', 'academic_year_id',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'is_absent'      => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
