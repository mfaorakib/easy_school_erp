<?php

namespace Modules\Dormitory\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

/** A student's dormitory room allocation for the active academic year. */
class StudentDormitory extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['student_id', 'dormitory_room_id', 'academic_year_id'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(DormitoryRoom::class, 'dormitory_room_id');
    }
}
