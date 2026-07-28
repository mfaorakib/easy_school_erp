<?php

namespace Modules\Examination\Services;

use App\Core\Support\Context;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSchedule;
use Modules\Examination\Models\ExamSeatPlan;
use Modules\Timetable\Models\Classroom;

/**
 * Generates an exam seat plan: gathers every student whose class/section has
 * a schedule under the exam, orders them (grouped by class, or interleaved
 * class-by-class when `mix_classes` is on — an adjacent-seat anti-cheating
 * measure), then fills the chosen rooms in order, `seats_per_bench` seats at
 * a time. Regenerating a plan replaces its rooms/assignments rather than
 * creating a second plan for the same exam.
 */
class SeatPlanService
{
    /**
     * @param  int[]  $classroomIds  in the order rooms should fill
     * @return array{plan:ExamSeatPlan, seated:int, unseated:int, total:int}
     */
    public function generate(Exam $exam, array $classroomIds, int $seatsPerBench, bool $mixClasses): array
    {
        return DB::transaction(function () use ($exam, $classroomIds, $seatsPerBench, $mixClasses) {
            $plan = ExamSeatPlan::updateOrCreate(
                ['exam_id' => $exam->id],
                [
                    'seats_per_bench' => max(1, $seatsPerBench),
                    'mix_classes'     => $mixClasses,
                    'academic_year_id' => Context::academicYearId(),
                ],
            );
            $plan->assignments()->delete();
            $plan->rooms()->delete();

            $ordered = $this->orderedStudents($exam, $mixClasses);
            $rooms = Classroom::whereIn('id', $classroomIds)->get()->keyBy('id');

            $index = 0;
            foreach (array_values($classroomIds) as $position => $classroomId) {
                $room = $rooms->get($classroomId);
                if (! $room) {
                    continue;
                }

                $plan->rooms()->create(['classroom_id' => $classroomId, 'position' => $position]);

                $capacity = $room->capacity ?: PHP_INT_MAX;
                for ($seat = 0; $seat < $capacity && $index < $ordered->count(); $seat++, $index++) {
                    $record = $ordered[$index];

                    $plan->assignments()->create([
                        'student_id'  => $record->student_id,
                        'classroom_id' => $classroomId,
                        'bench_no'    => intdiv($seat, $seatsPerBench) + 1,
                        'seat_no'     => ($seat % $seatsPerBench) + 1,
                        'roll_no'     => $record->roll_no,
                        'class_id'    => $record->class_id,
                        'section_id'  => $record->section_id,
                    ]);
                }
            }

            return [
                'plan'     => $plan->fresh(['rooms.classroom', 'assignments']),
                'seated'   => $index,
                'unseated' => $ordered->count() - $index,
                'total'    => $ordered->count(),
            ];
        });
    }

    /** Every live student record across the exam's scheduled class/section pairs, in seating order. */
    private function orderedStudents(Exam $exam, bool $mixClasses): Collection
    {
        $pairs = ExamSchedule::where('exam_id', $exam->id)
            ->select('class_id', 'section_id')->distinct()->get();

        $groups = $pairs
            ->map(fn ($pair) => StudentRecord::live()
                ->where('class_id', $pair->class_id)
                ->where('section_id', $pair->section_id)
                ->orderBy('roll_no')
                ->get())
            ->filter(fn ($records) => $records->isNotEmpty())
            ->values();

        if (! $mixClasses) {
            return $groups->flatten(1);
        }

        // Round-robin one student from each class/section group in turn, so
        // adjacent seats alternate between classes.
        $result = collect();
        $max = $groups->map->count()->max() ?? 0;
        for ($i = 0; $i < $max; $i++) {
            foreach ($groups as $group) {
                if (isset($group[$i])) {
                    $result->push($group[$i]);
                }
            }
        }

        return $result;
    }

    /** One room's seating, ordered by bench/seat, for the printable chart. */
    public function assignmentsForRoom(ExamSeatPlan $plan, int $classroomId): Collection
    {
        return $plan->assignments()
            ->where('classroom_id', $classroomId)
            ->with(['student', 'schoolClass', 'section'])
            ->orderBy('bench_no')->orderBy('seat_no')
            ->get();
    }

    /** A single student's seat within a plan, if seated. */
    public function assignmentForStudent(ExamSeatPlan $plan, int $studentId): ?\Modules\Examination\Models\ExamSeatAssignment
    {
        return $plan->assignments()
            ->where('student_id', $studentId)
            ->with(['classroom', 'schoolClass', 'section'])
            ->first();
    }
}
