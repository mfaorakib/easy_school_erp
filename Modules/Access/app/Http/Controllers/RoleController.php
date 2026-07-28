<?php

namespace Modules\Access\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\Services\RoleManagementService;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleManagementService $roles) {}

    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();

        return view('access::roles.index', compact('roles'));
    }

    public function create()
    {
        return view('access::roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:roles,name'],
        ]);

        $role = $this->roles->createRole($data['name']);

        return redirect()->route('access.roles.edit', $role)->with('status', 'Role created — now set its permissions.');
    }

    public function destroy(Role $role)
    {
        $this->roles->deleteRole($role);

        return redirect()->route('access.roles.index')->with('status', 'Role deleted.');
    }
}
