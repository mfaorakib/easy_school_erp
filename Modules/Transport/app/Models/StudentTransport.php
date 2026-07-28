<?php

namespace Modules\Transport\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

/** A student's route + vehicle assignment for the active academic year. */
class StudentTransport extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['student_id', 'transport_route_id', 'vehicle_id', 'academic_year_id'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
