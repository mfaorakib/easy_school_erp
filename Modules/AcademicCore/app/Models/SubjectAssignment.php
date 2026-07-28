<?php

namespace Modules\AcademicCore\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HumanResource\Models\Staff;

class SubjectAssignment extends Model
{
    use BelongsToAcademicYear;

    protected $fillable = ['class_id', 'section_id', 'subject_id', 'teacher_id', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }
}
