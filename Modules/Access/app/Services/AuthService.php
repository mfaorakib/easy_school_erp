<?php

namespace Modules\Access\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Login accepts email, username, or phone as the identifier
 * (the reference system LoginController behaviour) and requires an active account.
 */
class AuthService
{
    public function attempt(string $identifier, string $password, bool $remember = false): void
    {
        $field = $this->fieldFor($identifier);

        $ok = Auth::attempt(
            [$field => $identifier, 'password' => $password, 'is_active' => true],
            $remember
        );

        if (! $ok) {
            throw ValidationException::withMessages([
                'identifier' => __('These credentials do not match our records.'),
            ]);
        }
    }

    public function fieldFor(string $identifier): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Digits (optionally +, spaces, dashes) → phone; otherwise username.
        return preg_match('/^\+?[0-9\s\-]{6,}$/', $identifier) ? 'phone' : 'username';
    }
}
