<?php

namespace Modules\Access\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Access\Enums\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@easyschool.test'],
            [
                'name'      => 'Super Admin',
                'username'  => 'admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $admin->syncRoles([Role::SuperAdmin->value]);
    }
}
