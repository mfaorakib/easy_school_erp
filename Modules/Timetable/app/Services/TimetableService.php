<?php

namespace Modules\Timetable\Services;

use Illuminate\Support\Collection;
use Modules\Timetable\Models\ClassPeriod;
use Modules\Timetable\Models\TimetableEntry;

/**
 * Timetable assembly + editing.
 *
 * The weekly grid is rows = periods, columns = working days. Each cell is a
 * TimetableEntry (class/section on a day at a period → subject + optional
 * teacher/room). One entry per slot (enforced by a unique key); clearing a
 * cell's subject deletes the entry. A teacher assigned to two different
 * class/sections at the same day+period is a clash — reported, not blocked.
 */
class TimetableService
{
    /** @return string[] */
    public function days(): array
    {
        return TimetableEntry::DAYS;
    }

    public function periods(): Collection
    {
        return ClassPeriod::active()->ordered()->get();
    }

    /**
     * The weekly grid for a class/section.
     * @return array{periods:Collection, days:array, entries:Collection} entries keyed by "periodId|day"
     */
    public function classGrid(int $classId, int $sectionId): array
    {
        $entries = TimetableEntry::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with(['subject', 'teacher', 'classroom', 'period'])
            ->get()
            ->keyBy(fn ($e) => $e->class_period_id.'|'.$e->day);

        return ['periods' => $this->periods(), 'days' => $this->days(), 'entries' => $entries];
    }

    /**
     * Upsert one cell. An empty subject clears (deletes) the slot.
     * @param array{subject_id?:?int, teacher_id?:?int, classroom_id?:?int} $data
     */
    public function setEntry(int $classId, int $sectionId, string $day, int $periodId, array $data): ?TimetableEntry
    {
        $key = [
            'class_id'        => $classId,
            'section_id'      => $sectionId,
            'day'             => $day,
            'class_period_id' => $periodId,
        ];

        if (empty($data['subject_id'])) {
            TimetableEntry::where($key)->delete();

            return null;
        }

        return TimetableEntry::updateOrCreate($key, [
            'subject_id'   => $data['subject_id'],
            'teacher_id'   => $data['teacher_id'] ?? null,
            'classroom_id' => $data['classroom_id'] ?? null,
        ]);
    }

    /**
     * Teacher double-bookings within a class/section's saved grid: any entry whose
     * teacher is also booked by a *different* class/section at the same day+period.
     * @return array<int, array{entry:TimetableEntry, conflict:TimetableEntry}>
     */
    public function clashesFor(int $classId, int $sectionId): array
    {
        $entries = TimetableEntry::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereNotNull('teacher_id')
            ->with(['period', 'subject'])
            ->get();

        $clashes = [];
        foreach ($entries as $entry) {
            $conflict = TimetableEntry::where('teacher_id', $entry->teacher_id)
                ->where('day', $entry->day)
                ->where('class_period_id', $entry->class_period_id)
                ->where('id', '!=', $entry->id)
                ->with(['schoolClass', 'section'])
                ->first();

            if ($conflict) {
                $clashes[] = ['entry' => $entry, 'conflict' => $conflict];
            }
        }

        return $clashes;
    }

    /**
     * A teacher's own weekly schedule.
     * @return array{periods:Collection, days:array, entries:Collection} entries keyed by "periodId|day"
     */
    public function teacherGrid(int $teacherId): array
    {
        $entries = TimetableEntry::where('teacher_id', $teacherId)
            ->with(['subject', 'schoolClass', 'section', 'classroom', 'period'])
            ->get()
            ->keyBy(fn ($e) => $e->class_period_id.'|'.$e->day);

        return ['periods' => $this->periods(), 'days' => $this->days(), 'entries' => $entries];
    }
}
