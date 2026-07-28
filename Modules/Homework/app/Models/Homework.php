<?php

namespace Modules\Homework\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\HumanResource\Models\Staff;

class Homework extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $table = 'homeworks';

    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'teacher_id', 'title', 'description',
        'homework_date', 'submission_date', 'evaluation_marks', 'academic_year_id', 'created_by',
    ];

    protected $casts = [
        'homework_date'    => 'date',
        'submission_date'  => 'date',
        'evaluation_marks' => 'decimal:2',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(HomeworkStudent::class);
    }

    public function evaluatedCount(): int
    {
        return $this->students()->where('is_complete', true)->count();
    }
}
