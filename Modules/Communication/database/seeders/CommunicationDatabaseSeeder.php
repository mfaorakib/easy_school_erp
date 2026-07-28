<?php

namespace Modules\Communication\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Communication\Models\Event;
use Modules\Communication\Models\Notice;

class CommunicationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Notice::firstOrCreate(
            ['title' => 'Welcome to the new academic year'],
            ['description' => 'Classes resume as scheduled. Please check the timetable.',
             'notice_date' => now()->toDateString(), 'publish_date' => now()->toDateString(),
             'audiences' => ['all'], 'is_published' => true],
        );
        Notice::firstOrCreate(
            ['title' => 'Parent-Teacher Meeting'],
            ['description' => 'PTM this Friday for all sections.',
             'notice_date' => now()->toDateString(), 'audiences' => ['teacher', 'parent'], 'is_published' => true],
        );

        Event::firstOrCreate(
            ['title' => 'Annual Sports Day'],
            ['description' => 'Inter-house sports competition.',
             'start_date' => now()->addDays(20)->toDateString(), 'location' => 'School Ground'],
        );
    }
}
