<?php

namespace Modules\Timetable\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\HumanResource\Models\Staff;

/** One cell of a class/section's weekly timetable. Year-scoped. */
class TimetableEntry extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'class_id', 'section_id', 'day', 'class_period_id',
        'subject_id', 'teacher_id', 'classroom_id', 'academic_year_id',
    ];

    /** The working week (Friday is the default weekend, so excluded). */
    public const DAYS = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ClassPeriod::class, 'class_period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
