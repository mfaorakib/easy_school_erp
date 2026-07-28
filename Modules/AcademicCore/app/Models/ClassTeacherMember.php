<?php

namespace Modules\AcademicCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HumanResource\Models\Staff;

class ClassTeacherMember extends Model
{
    protected $fillable = ['class_teacher_assignment_id', 'teacher_id'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ClassTeacherAssignment::class, 'class_teacher_assignment_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }
}
