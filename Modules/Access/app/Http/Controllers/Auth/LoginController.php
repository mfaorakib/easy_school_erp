<?php

namespace Modules\Access\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Access\Enums\Role;
use Modules\Access\Http\Requests\LoginRequest;
use Modules\Access\Services\AuthService;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function show()
    {
        return view('access::auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $this->auth->attempt(
            $request->string('identifier'),
            $request->string('password'),
            $request->boolean('remember'),
        );

        $request->session()->regenerate();

        // Guardians and students land in their own portal (their own/their
        // children's info only), never the school-wide admin dashboard —
        // everyone else is unaffected.
        $home = Auth::user()->hasAnyRole([Role::Parents->value, Role::Student->value]) ? '/portal' : '/dashboard';

        return redirect()->intended($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
