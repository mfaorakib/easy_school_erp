<?php

namespace Modules\Foundation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Foundation\Models\FeatureFlag;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        // Module registry (the reference system module list, minus SaaS subscription/billing).
        $modules = [
            'AcademicCore'  => 'Academic Core',
            'Attendance'    => 'Attendance',
            'Fees'          => 'Fees Collection',
            'Examination'   => 'Examination',
            'Homework'      => 'Homework',
            'Lesson'        => 'Lesson Plan',
            'Library'       => 'Library',
            'Inventory'     => 'Inventory',
            'Transport'     => 'Transport',
            'Dormitory'     => 'Dormitory',
            'Wallet'        => 'Wallet',
            'Communication' => 'Communication',
            'BehaviourRecords' => 'Behaviour Records',
            'DownloadCenter'   => 'Download Center',
            'Reports'       => 'Reports',
            'Builder'       => 'Frontend Builder',
        ];

        foreach ($modules as $module => $label) {
            FeatureFlag::updateOrCreate(
                ['module' => $module],
                ['label' => $label, 'enabled' => true]
            );
        }
    }
}
