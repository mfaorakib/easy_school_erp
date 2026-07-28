<?php

namespace Modules\Foundation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Foundation\Models\BaseGroup;

class BaseGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'gender'      => ['Gender', ['Male', 'Female', 'Others']],
            'religion'    => ['Religion', ['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Others']],
            'blood_group' => ['Blood Group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']],
        ];

        foreach ($groups as $slug => [$name, $values]) {
            $group = BaseGroup::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_system' => true]
            );

            foreach ($values as $i => $value) {
                $group->setups()->updateOrCreate(
                    ['name' => $value],
                    ['is_active' => true, 'position' => $i]
                );
            }
        }
    }
}
