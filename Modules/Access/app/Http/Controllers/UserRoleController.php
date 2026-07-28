<?php

namespace Modules\Access\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Access\Services\RoleManagementService;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function __construct(private readonly RoleManagementService $roles) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $users = User::with('roles')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%")))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('access::users.index', compact('users', 'q'));
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $allRoles = Role::orderBy('name')->get();

        return view('access::users.edit', compact('user', 'allRoles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'roles'    => ['array'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $this->roles->assignRoles($user, $data['roles'] ?? []);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        return redirect()->route('access.users.index')->with('status', 'Roles updated for '.$user->name.'.');
    }
}
