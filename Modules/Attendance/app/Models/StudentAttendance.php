<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;
use Modules\Attendance\Enums\AttendanceStatus;

class StudentAttendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'class_id', 'section_id', 'subject_id',
        'academic_year_id', 'date', 'status', 'note',
    ];

    protected $casts = [
        'date'   => 'date',
        'status' => AttendanceStatus::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
