<?php

namespace Modules\AcademicCore\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HumanResource\Models\Staff;

/** Class-teacher slot: one per class+section+year, many teachers under it. */
class ClassTeacherAssignment extends Model
{
    use BelongsToAcademicYear;

    protected $fillable = ['class_id', 'section_id', 'academic_year_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ClassTeacherMember::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Staff::class, 'class_teacher_members', 'class_teacher_assignment_id', 'teacher_id');
    }
}
