<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class LocalizationController extends Controller
{
    public function edit(SettingsService $settings)
    {
        $s = $settings->group('localization');
        $weekend = $settings->getArray('weekend_days');

        return view('settings::localization', compact('s', 'weekend') + [
            'timezones'   => $settings->timezones(),
            'dateFormats' => $settings->dateFormats(),
            'weekDays'    => $settings->weekDays(),
            'locales'     => config('locales.available'),
        ]);
    }

    public function update(Request $request, SettingsService $settings)
    {
        $request->validate([
            'timezone'         => ['required', 'string', 'timezone'],
            'date_format'      => ['required', 'string', 'max:20'],
            'default_language' => ['required', 'string', 'max:5'],
            'weekend_days'     => ['array'],
            'weekend_days.*'   => ['string'],
        ]);

        $settings->setMany([
            'timezone'         => $request->timezone,
            'date_format'      => $request->date_format,
            'default_language' => $request->default_language,
            'weekend_days'     => $request->input('weekend_days', []),
        ], 'localization');

        return redirect()->route('settings.localization')->with('status', 'Settings saved.');
    }
}
