<?php

namespace Modules\Access\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\Access\Enums\Role;

class ProfileController extends Controller
{
    /**
     * Every logged-in user — any role, admin or guardian — lands here via the
     * same route; only the wrapping layout differs (mirrors LoginController's
     * role-based home redirect).
     */
    public function edit(Request $request)
    {
        $view = $request->user()->hasRole(Role::Parents->value) ? 'guardianportal::profile' : 'profile.edit';

        return view($view, ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
        ]);

        $user->update($data);

        return back()->with('status', __('ui.profile_updated'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => Hash::make($request->string('password'))]);

        return back()->with('status', __('ui.password_updated'));
    }
}
