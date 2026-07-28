# 05 — Localization / Multi-Language

How the reference system implements multi-language, and a recommended clean approach for EasySchool ERP.

---

## 1. How the reference system Does It

### Summary: file-based translations + DB metadata (NOT a DB phrase table)

Despite the presence of `SmLanguagePhrase` model + `sm_language_phrases` table, the reference system's live
translation is **100% Laravel-native file-based** (`resources/lang/{locale}/*.php`) rendered with
`__()` / `@lang()` / `trans()` (~15,000 usages across ~738 blade files). The DB tables only hold
**metadata** (which languages exist, which is active, RTL flag) and per-user preference.

The `sm_language_phrases` table (`modules`, `default_phrases`, `en`, `es`, `bn`, `fr`, `school_id`)
is **legacy/dead code**: the `SmLanguagePhrase` model is empty (`app/SmLanguagePhrase.php`), it is
never queried anywhere in `app/`, and only appears in the migration + an old seeder under
`database/seeders/z_old/`. Do NOT replicate it.

### The moving parts

| Piece | Location | Role |
|---|---|---|
| Translation files | `resources/lang/{locale}/*.php` (en, ar, bn, be, ca, es, fr, …) + `Modules/{X}/Resources/lang/{locale}/` | Actual strings, keyed by file: `__('common.success')` |
| `SmLanguage` (empty model) | `app/SmLanguage.php`; table `sm_languages` | Installed languages: `language_name`, `language_universal` (locale code), `native`, `lang_id`, `active_status`, `school_id`. Drives the switcher list and which is the school default. |
| `Language` (empty model) | `app/Language.php`; table `languages` | Language master: `name`, `code`, `native`, `rtl` (bool), `school_id`. The `rtl` flag lives here. |
| Users table | `2014_12_01_000003_create_users_table.php` | `language` (string, default `'en'`) and `rtl_ltl` (int, `1`=RTL, `2`=LTR, default `2`) columns store **per-user** preference. |
| `Localization` middleware | `app/Http/Middleware/Localization.php` | Calls `App::setLocale(getUserLanguage())` per request; also warms a 6-hour `translations` cache of all strings as JSON (for front-end JS `window._translations`). |
| Helpers | `app/Helpers/Helper.php` | `getUserLanguage()`, `userLanguage()`, `userRtlLtl()` — resolve locale/direction from session → user → admin fallback. |
| Blade layout | `resources/views/backEnd/partials/header.blade.php` | `<html lang="{{ app()->getLocale() }}" @if(userRtlLtl()==1) dir="rtl" class="rtl" @endif>`; also swaps in `bootstrap.rtl.min.css` + `global_rtl.css` when RTL. |
| Switch controllers | `SmSystemSettingController@ajaxUserLanguageChange`, `@changeLanguage`, `@themeStyleRTL`; API v2 `Language\LanguageController` | Persist new `language`/`rtl_ltl` to user, update session keys, clear caches. |
| Import/Export | `app/Http/Controllers/LanguageController.php` | Zips/unzips `resources/lang/{locale}` for install/backup. In-app phrase editor (`getTranslationTerms`/`translationTermUpdate`) reads a lang file, lets admin edit values, and **writes the file back** with `file_put_contents(... var_export(...))`. |

### Locale resolution flow (per request)

1. `Localization` middleware runs, calls `App::setLocale(getUserLanguage())`.
2. `getUserLanguage()`:
   - if logged in → `userLanguage()` → `session('user_language')` else `Auth::user()->language` (cached to session).
   - if guest → the school's admin (`role_id=1`) user's `language`, else `'en'`.
3. Direction: `userRtlLtl()` → `session('user_text_direction')` else the admin user's `rtl_ltl` (1/2).

### Switching language (logged-in user)

`ajaxUserLanguageChange($request)`:
- looks up `Language::where('code', $id)`, sets `$user->language = $id`.
- if admin (`role_id==1`) → `setDefaultLanguge()` cascades the language to **all** users of the school
  and flips the school default (`sm_languages.active_status`).
- sets `rtl_ltl` from `$lang->rtl`, updates `session('user_text_direction')`,
  `session('user_language')`, `session('locale')`, and clears the `translations` cache.

### RTL support

- Direction is a **numeric** column `rtl_ltl` (1=RTL, 2=LTR) on users, derived from `languages.rtl`.
- Blade: `@if(userRtlLtl()==1) dir="rtl" class="rtl" @endif` on `<html>`, plus loading separate
  RTL bootstrap + `global_rtl.css` stylesheets. JS gets `window._rtl = true/false`.

### Multi-tenancy note

Everything is scoped by `school_id` (SaaS/multi-school). Language install/default is per-school.

### What's weak about it (improve in EasySchool)

- Two overlapping tables (`sm_languages` + `languages`) for one concept; empty "model" classes.
- Direction stored as a magic `1/2` int instead of a boolean/`ltr|rtl` string derived from the locale.
- Admin changing language **force-overwrites every user's** preference (surprising side effect).
- Dead `sm_language_phrases` table/model.
- In-app file rewriting via `var_export` + `recurse_copy` over `base_path()` is fragile/risky.
- Direction and locale are decoupled (a user can end up RTL text with an LTR locale).

---

## 2. Recommended Clean Approach for EasySchool ERP

Keep Laravel-native file-based translations (the one genuinely good part of the reference system), drop the DB
phrase table, and make **direction a property of the locale** (single source of truth in config).

### Config: `config/localization.php`

```php
<?php
return [
    'default' => 'en',
    'fallback' => 'en',

    // Single source of truth for available languages + direction.
    'available' => [
        'en' => ['name' => 'English',  'native' => 'English',   'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic',   'native' => 'العربية',   'dir' => 'rtl'],
        'fr' => ['name' => 'French',   'native' => 'Français',  'dir' => 'ltr'],
        'bn' => ['name' => 'Bengali',  'native' => 'বাংলা',      'dir' => 'ltr'],
    ],
];
```

Helper for direction (in a `helpers.php` or a small `Localization` service):

```php
function locale_dir(?string $locale = null): string {
    $locale = $locale ?: app()->getLocale();
    return config("localization.available.$locale.dir", 'ltr');
}
function is_rtl(?string $locale = null): bool {
    return locale_dir($locale) === 'rtl';
}
```

### Users: a single `locale` column (no `rtl_ltl`)

Migration:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('locale', 8)->default('en')->after('email');
});
```

Direction is **derived** from the locale via config — never stored separately. This removes the
"RTL text in an LTR locale" bug class entirely.

### Middleware: `app/Http/Middleware/SetLocale.php`

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $available = array_keys(config('localization.available'));

        $locale = $request->session()->get('locale')
            ?? optional($request->user())->locale
            ?? $request->getPreferredLanguage($available)   // Accept-Language for guests
            ?? config('localization.default');

        if (! in_array($locale, $available, true)) {
            $locale = config('localization.default');
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
```

Register in `bootstrap/app.php` (Laravel 11+) / `Kernel.php` `web` group, after `StartSession`.

### Language switcher

Route:

```php
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
```

Controller `app/Http/Controllers/LocaleController.php`:

```php
public function update(Request $request)
{
    $data = $request->validate([
        'locale' => ['required', Rule::in(array_keys(config('localization.available')))],
    ]);

    $request->session()->put('locale', $data['locale']);

    if ($user = $request->user()) {            // only the current user — no cascade
        $user->update(['locale' => $data['locale']]);
    }

    return back();
}
```

Blade partial `resources/views/partials/language-switcher.blade.php`:

```blade
<form method="POST" action="{{ route('locale.update') }}">
    @csrf
    <select name="locale" onchange="this.form.submit()">
        @foreach(config('localization.available') as $code => $lang)
            <option value="{{ $code }}" @selected(app()->getLocale() === $code)>
                {{ $lang['native'] }}
            </option>
        @endforeach
    </select>
</form>
```

### RTL via `dir` in the layout

```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ locale_dir() }}">
<head>
    ...
    @if(is_rtl())
        <link rel="stylesheet" href="{{ asset('css/app.rtl.css') }}">
    @endif
</head>
```

Prefer CSS **logical properties** (`margin-inline-start`, `padding-inline`, `text-align: start`,
`inset-inline`) so a single stylesheet works both directions and you rarely need a separate RTL CSS
build. Use `dir="rtl"` on `<html>` as the switch; components then flip automatically.

### Translation files & usage

- `lang/en/common.php`, `lang/en/students.php`, `lang/ar/common.php`, … (Laravel 11 uses top-level
  `lang/`; Laravel ≤10 uses `resources/lang/`). Publish with `php artisan lang:publish` if needed.
- In blade use `{{ __('students.created') }}` / `@lang('common.save')`; validation/pagination/auth
  via the standard Laravel files.
- Keep keys **namespaced by domain** (`students.`, `fees.`, `exam.`) — do not dump everything in one file.
- Optional: expose strings to JS by encoding the loaded arrays to JSON in a `@json` blob or a small
  `/js/lang.js` endpoint (only if the SPA/JS layer needs them — don't cache-warm eagerly like the reference system).

### Optional: admin-editable translations

If non-developers must edit strings, prefer a package (e.g. `spatie/laravel-translation-loader`
with a `database` loader, or `outhebox/laravel-translations`) instead of rewriting `.php` files with
`var_export`. Keep file-based as the source of truth in git; DB overrides layer on top.

---

## 3. Implementation Checklist

- [ ] Add `config/localization.php` with `default`, `fallback`, and `available` (name/native/dir per locale).
- [ ] Add `locale_dir()` / `is_rtl()` helpers (autoloaded `app/helpers.php` or a `Localization` service).
- [ ] Migration: add `locale` (string, default `en`) to `users`; **do not** add an `rtl_ltl` column.
- [ ] Create `app/Http/Middleware/SetLocale.php` and register it in the `web` middleware group (after session).
- [ ] Create `LocaleController@update` + `POST /locale` route (updates session + current user only, no cascade).
- [ ] Add `language-switcher` blade partial driven by `config('localization.available')`.
- [ ] Set `<html lang dir="{{ locale_dir() }}">` in the master layout; add RTL stylesheet conditionally (or use logical CSS properties).
- [ ] Create `lang/{locale}/*.php` files namespaced by domain (`common`, `students`, `fees`, `exam`, `validation`, …).
- [ ] Replace hard-coded UI strings with `__('domain.key')` progressively; keep `en` as the complete key set / fallback.
- [ ] (If SaaS/multi-tenant) decide scope: a per-school **default** locale + per-user override; store school default in a settings table, resolve as another fallback layer in `SetLocale`.
- [ ] (Optional) DB-backed editable translations via a package — keep git files as source of truth.
- [ ] Add a `validation`/`auth`/`pagination`/`passwords` translation set per locale for framework messages.
- [ ] Tests: middleware picks user locale; switcher persists; unknown locale falls back; `dir` flips for RTL locale.
