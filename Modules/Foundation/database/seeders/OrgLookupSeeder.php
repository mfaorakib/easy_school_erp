<?php

namespace Modules\Foundation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Foundation\Models\Department;
use Modules\Foundation\Models\Designation;

class OrgLookupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Principal', 'Vice Principal', 'Head Teacher', 'Teacher', 'Accountant', 'Librarian', 'Receptionist', 'Driver'] as $title) {
            Designation::updateOrCreate(['title' => $title], ['is_active' => true]);
        }

        foreach (['Administration', 'Academic', 'Accounts', 'Library', 'Transport'] as $name) {
            Department::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
