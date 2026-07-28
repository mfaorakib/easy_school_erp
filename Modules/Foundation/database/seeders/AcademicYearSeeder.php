<?php

namespace Modules\Foundation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Foundation\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');

        AcademicYear::updateOrCreate(
            ['year' => $year],
            [
                'title'      => $year,
                'start_date' => "$year-01-01",
                'end_date'   => "$year-12-31",
                'is_active'  => true,
            ]
        );
    }
}
