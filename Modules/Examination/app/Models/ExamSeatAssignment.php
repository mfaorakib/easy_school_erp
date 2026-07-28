<?php

namespace Modules\Examination\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Student;
use Modules\Timetable\Models\Classroom;

/** One student's seat (room + bench + seat) for one exam seat plan. */
class ExamSeatAssignment extends Model
{
    protected $fillable = [
        'exam_seat_plan_id', 'student_id', 'classroom_id',
        'bench_no', 'seat_no', 'roll_no', 'class_id', 'section_id',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ExamSeatPlan::class, 'exam_seat_plan_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
