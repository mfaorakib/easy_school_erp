<?php

namespace Modules\Timetable\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\HumanResource\Models\Staff;
use Modules\Timetable\Models\Classroom;
use Modules\Timetable\Models\ClassPeriod;
use Modules\Timetable\Services\TimetableService;

class TimetableDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            ['name' => '1st Period', 'start_time' => '09:00', 'end_time' => '09:45', 'is_break' => false],
            ['name' => '2nd Period', 'start_time' => '09:45', 'end_time' => '10:30', 'is_break' => false],
            ['name' => 'Tiffin Break', 'start_time' => '10:30', 'end_time' => '11:00', 'is_break' => true],
            ['name' => '3rd Period', 'start_time' => '11:00', 'end_time' => '11:45', 'is_break' => false],
            ['name' => '4th Period', 'start_time' => '11:45', 'end_time' => '12:30', 'is_break' => false],
        ];
        $created = [];
        foreach ($periods as $i => $p) {
            $created[$p['name']] = ClassPeriod::firstOrCreate(['name' => $p['name']], $p + ['position' => $i + 1]);
        }

        foreach (['101', '102'] as $no) {
            Classroom::firstOrCreate(['room_no' => $no], ['capacity' => 40]);
        }

        // A small starter routine for Class 1 / Section A on Saturday & Sunday.
        $class = SchoolClass::where('name', 'Class 1')->first();
        $section = Section::where('name', 'A')->first();
        $subject = Subject::orderBy('id')->first();
        $room = Classroom::first();
        $teacher = Staff::where('is_active', true)->orderBy('id')->first();

        if ($class && $section && $subject) {
            $svc = app(TimetableService::class);
            foreach (['saturday', 'sunday'] as $day) {
                foreach (['1st Period', '2nd Period'] as $pname) {
                    $svc->setEntry($class->id, $section->id, $day, $created[$pname]->id, [
                        'subject_id'   => $subject->id,
                        'teacher_id'   => optional($teacher)->id,
                        'classroom_id' => optional($room)->id,
                    ]);
                }
            }
        }
    }
}
