<?php

namespace Modules\Access\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Enums\Role as RoleEnum;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::updateOrCreate(
                ['id' => $role->id()],
                ['name' => $role->value, 'guard_name' => 'web'],
            );
        }
    }
}
