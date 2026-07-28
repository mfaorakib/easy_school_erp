<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class EmailSettingController extends Controller
{
    public function edit(SettingsService $settings)
    {
        $s = $settings->group('email');

        return view('settings::email', compact('s'));
    }

    public function update(Request $request, SettingsService $settings)
    {
        $request->validate([
            'mail_host'         => ['nullable', 'string', 'max:255'],
            'mail_port'         => ['nullable', 'string', 'max:10'],
            'mail_username'     => ['nullable', 'string', 'max:255'],
            'mail_password'     => ['nullable', 'string', 'max:255'],
            'mail_encryption'   => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name'    => ['nullable', 'string', 'max:255'],
        ]);

        $settings->setMany($request->only(
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name'
        ), 'email');

        return redirect()->route('settings.email')->with('status', 'Settings saved.');
    }
}
