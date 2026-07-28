<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class SmsSettingController extends Controller
{
    public function edit(SettingsService $settings)
    {
        $s = $settings->group('sms');

        return view('settings::sms', compact('s'));
    }

    public function update(Request $request, SettingsService $settings)
    {
        $request->validate([
            'sms_provider'  => ['nullable', 'string', 'max:60'],
            'sms_api_key'   => ['nullable', 'string', 'max:255'],
            'sms_sender_id' => ['nullable', 'string', 'max:60'],
        ]);

        $settings->setMany($request->only(
            'sms_provider',
            'sms_api_key',
            'sms_sender_id'
        ), 'sms');

        return redirect()->route('settings.sms')->with('status', 'Settings saved.');
    }
}
