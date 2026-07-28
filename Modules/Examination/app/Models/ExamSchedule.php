<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;

class ExamSchedule extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'exam_id', 'class_id', 'section_id', 'subject_id',
        'exam_date', 'start_time', 'end_time', 'room',
        'full_mark', 'pass_mark', 'academic_year_id',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'full_mark' => 'decimal:2',
        'pass_mark' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
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
