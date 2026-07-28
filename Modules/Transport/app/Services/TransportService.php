<?php

namespace Modules\Transport\Services;

use App\Core\Support\Context;
use Modules\Transport\Models\StudentTransport;

/**
 * Transport assignment. A student has at most one route+vehicle per academic
 * year (upserted). Vehicle→route links are managed via the route's pivot.
 */
class TransportService
{
    public function assignStudent(int $studentId, int $routeId, ?int $vehicleId = null): StudentTransport
    {
        return StudentTransport::updateOrCreate(
            ['student_id' => $studentId, 'academic_year_id' => Context::academicYearId()],
            ['transport_route_id' => $routeId, 'vehicle_id' => $vehicleId],
        );
    }

    public function unassignStudent(int $studentId): void
    {
        StudentTransport::where('student_id', $studentId)
            ->where('academic_year_id', Context::academicYearId())
            ->delete();
    }
}
