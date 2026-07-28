<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active UI language for every request:
 *   session('locale')  →  authenticated user's `locale`  →  config default.
 * Only locales listed in config('locales.available') are honoured.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('locales.available', ['en' => []]));

        $locale = session('locale')
            ?? optional($request->user())->locale
            ?? config('app.locale');

        if (! in_array($locale, $available, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
