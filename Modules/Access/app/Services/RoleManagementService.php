<?php

namespace Modules\Access\Services;

use App\Models\User;
use Modules\Access\Enums\Role as RoleEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role/permission administration. The 9 fixed system roles (see Role enum)
 * can be RE-PERMISSIONED freely but never renamed or deleted — business
 * logic elsewhere depends on their exact slugs existing (e.g. the Guardian
 * Portal's `role:parent` gate, Staff::scopeTeachers()).
 */
class RoleManagementService
{
    public function createRole(string $name): Role
    {
        return Role::create(['name' => $name, 'guard_name' => 'web']);
    }

    /** @param  string[]  $permissionNames */
    public function updatePermissions(Role $role, array $permissionNames): void
    {
        $role->syncPermissions(Permission::whereIn('name', $permissionNames)->get());
    }

    public function deleteRole(Role $role): void
    {
        abort_if($this->isSystemRole($role), 422, 'This role is built into the system and cannot be deleted.');

        $role->delete();
    }

    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, array_map(fn ($r) => $r->value, RoleEnum::cases()), true);
    }

    /** @param  string[]  $roleNames */
    public function assignRoles(User $user, array $roleNames): void
    {
        $user->syncRoles(Role::whereIn('name', $roleNames)->get());
    }
}
