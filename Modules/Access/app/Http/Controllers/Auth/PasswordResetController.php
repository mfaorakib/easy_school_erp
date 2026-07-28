<?php

namespace Modules\Access\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\Access\Services\AuthService;

class PasswordResetController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function showLinkRequestForm()
    {
        return view('access::auth.forgot-password');
    }

    /**
     * Accepts the same email/username/phone identifier as login. The
     * response is intentionally identical whether or not an account was
     * found, or whether it has an email on file, so this can't be used to
     * enumerate accounts.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['identifier' => ['required', 'string']]);

        $identifier = $request->string('identifier')->toString();
        $field = $this->auth->fieldFor($identifier);
        $user = User::where($field, $identifier)->first();

        if ($user && $user->email) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', __('ui.reset_link_sent'));
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('access::auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->update(['password' => Hash::make($password)]);
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('status', __('ui.reset_password_success'));
    }
}
