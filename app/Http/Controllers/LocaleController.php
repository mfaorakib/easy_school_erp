<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /** Switch UI language and persist it (session + user profile). */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (array_key_exists($locale, config('locales.available', []))) {
            session(['locale' => $locale]);

            if ($user = $request->user()) {
                $user->update(['locale' => $locale]);
            }
        }

        return back();
    }
}
