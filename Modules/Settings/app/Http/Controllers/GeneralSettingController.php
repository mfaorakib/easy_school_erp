<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class GeneralSettingController extends Controller
{
    public function edit(SettingsService $settings)
    {
        $s = $settings->group('general');

        return view('settings::general', compact('s'));
    }

    public function update(Request $request, SettingsService $settings)
    {
        $request->validate([
            'school_name'     => ['required', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:60'],
            'email'           => ['nullable', 'email', 'max:255'],
            'currency_code'   => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
        ]);

        $settings->setMany(
            $request->only('school_name', 'address', 'phone', 'email', 'currency_code', 'currency_symbol'),
            'general'
        );

        return redirect()->route('settings.general')->with('status', 'Settings saved.');
    }
}
