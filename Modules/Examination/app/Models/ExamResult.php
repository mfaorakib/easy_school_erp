<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

class ExamResult extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'exam_id', 'student_id', 'class_id', 'section_id',
        'total_marks', 'total_full_mark', 'gpa', 'grade', 'is_pass', 'position', 'academic_year_id',
    ];

    protected $casts = [
        'total_marks'     => 'decimal:2',
        'total_full_mark' => 'decimal:2',
        'gpa'             => 'decimal:2',
        'is_pass'         => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
