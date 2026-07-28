<?php

namespace Modules\Admission\Database\Seeders;

use App\Core\Support\Context;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Foundation\Services\SettingService;

class AdmissionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(SettingService::class)->set(
            'admission.id_format',
            'STU-{YYYY}-{SEQ:4}',
            'string',
            'admission'
        );

        $class = SchoolClass::orderBy('position')->first();
        if (! $class) {
            return;
        }

        $demo = [
            ['first_name' => 'Rifat', 'last_name' => 'Hasan', 'guardian_name' => 'Kamal Hasan', 'guardian_mobile' => '01711000111'],
            ['first_name' => 'Anika', 'last_name' => 'Islam', 'guardian_name' => 'Nasrin Islam', 'guardian_mobile' => '01711000222'],
        ];

        foreach ($demo as $row) {
            if (AdmissionApplication::where('first_name', $row['first_name'])->where('last_name', $row['last_name'])->exists()) {
                continue;
            }

            AdmissionApplication::create($row + [
                'reference_no'     => 'APP-'.strtoupper(Str::random(8)),
                'guardian_relation' => 'Father',
                'present_address'  => 'Dhaka, Bangladesh',
                'desired_class_id' => $class->id,
                'status'           => AdmissionApplication::STATUS_PENDING,
                'academic_year_id' => Context::academicYearId(),
            ]);
        }
    }
}
