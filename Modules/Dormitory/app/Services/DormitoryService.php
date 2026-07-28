<?php

namespace Modules\Dormitory\Services;

use App\Core\Support\Context;
use Modules\Dormitory\Models\DormitoryRoom;
use Modules\Dormitory\Models\StudentDormitory;
use RuntimeException;

/**
 * Dormitory room allocation. One allocation per student per academic year;
 * a room cannot be filled beyond its bed capacity.
 */
class DormitoryService
{
    public function assignStudent(int $studentId, int $roomId): StudentDormitory
    {
        $yearId = Context::academicYearId();
        $room   = DormitoryRoom::findOrFail($roomId);

        $alreadyHere = StudentDormitory::where('student_id', $studentId)
            ->where('academic_year_id', $yearId)
            ->where('dormitory_room_id', $roomId)->exists();

        if (! $alreadyHere && $room->free_beds < 1) {
            throw new RuntimeException("Room {$room->room_no} is full (capacity {$room->capacity}).");
        }

        return StudentDormitory::updateOrCreate(
            ['student_id' => $studentId, 'academic_year_id' => $yearId],
            ['dormitory_room_id' => $roomId],
        );
    }

    public function unassignStudent(int $studentId): void
    {
        StudentDormitory::where('student_id', $studentId)
            ->where('academic_year_id', Context::academicYearId())
            ->delete();
    }
}
