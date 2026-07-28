<?php

namespace Modules\HumanResource\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Access\Enums\Role;
use Modules\HumanResource\Models\Staff;

class HumanResourceDatabaseSeeder extends Seeder
{
    /** A couple of demo teachers so class-teacher / subject-assignment are usable. */
    public function run(): void
    {
        $teachers = [
            ['name' => 'Rahim Uddin', 'email' => 'rahim@easyschool.test'],
            ['name' => 'Fatema Khatun', 'email' => 'fatema@easyschool.test'],
        ];

        foreach ($teachers as $t) {
            $user = User::firstOrCreate(
                ['email' => $t['email']],
                ['name' => $t['name'], 'password' => Hash::make('password'), 'is_active' => true],
            );
            $user->syncRoles([Role::Teacher->value]);

            Staff::firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $t['name'], 'first_name' => explode(' ', $t['name'])[0], 'is_active' => true],
            );
        }
    }
}
