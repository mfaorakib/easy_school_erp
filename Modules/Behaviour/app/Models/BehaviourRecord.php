<?php

namespace Modules\Behaviour\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

class BehaviourRecord extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'student_id', 'behaviour_type_id', 'incident_date', 'comment', 'points', 'academic_year_id', 'created_by',
    ];

    protected $casts = ['incident_date' => 'date', 'points' => 'decimal:2'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BehaviourType::class, 'behaviour_type_id');
    }
}
