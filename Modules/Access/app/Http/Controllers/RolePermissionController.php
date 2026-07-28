<?php

namespace Modules\Access\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\Services\RoleManagementService;
use Modules\Access\Support\PermissionRegistry;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function __construct(private readonly RoleManagementService $roles) {}

    public function edit(Role $role)
    {
        $role->load('permissions');
        $groups = PermissionRegistry::all();
        $current = $role->permissions->pluck('name')->all();

        return view('access::roles.edit', compact('role', 'groups', 'current'));
    }

    public function update(Request $request, Role $role)
    {
        $selected = array_keys((array) $request->input('permissions', []));

        $this->roles->updatePermissions($role, $selected);

        return redirect()->route('access.roles.edit', $role)->with('status', 'Permissions updated.');
    }
}
