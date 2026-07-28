<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class AppearanceController extends Controller
{
    public function edit(SettingsService $settings)
    {
        $s = $settings->group('appearance');

        return view('settings::appearance', compact('s'));
    }

    public function update(Request $request, SettingsService $settings)
    {
        $request->validate([
            'admin_primary_color' => ['required', 'string', 'max:20'],
            'admin_theme'         => ['required', 'in:light,dark'],
        ]);

        $settings->setMany($request->only('admin_primary_color', 'admin_theme'), 'appearance');

        return redirect()->route('settings.appearance')->with('status', 'Settings saved.');
    }
}
