<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\SiteSetting;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::current();

        return view('builder::settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'       => ['required', 'string', 'max:255'],
            'tagline'         => ['nullable', 'string', 'max:255'],
            'primary_color'   => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'phone'           => ['nullable', 'string', 'max:60'],
            'email'           => ['nullable', 'email', 'max:255'],
            'address'         => ['nullable', 'string', 'max:255'],
            'facebook'        => ['nullable', 'string', 'max:255'],
            'twitter'         => ['nullable', 'string', 'max:255'],
            'youtube'         => ['nullable', 'string', 'max:255'],
            'linkedin'        => ['nullable', 'string', 'max:255'],
            'footer_text'     => ['nullable', 'string', 'max:500'],
            'logo'            => ['nullable', 'image', 'max:4096'],
        ]);

        $settings = SiteSetting::current();

        $data = $request->except('logo', '_token', '_method');

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('builder', 'public');
        }

        $settings->update($data);

        return redirect()->route('builder.settings.edit')->with('status', 'Settings saved.');
    }
}
