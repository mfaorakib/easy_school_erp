<?php

namespace Modules\Admission\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Foundation\Services\SettingService;
use Modules\Foundation\Support\IdPattern;

class AdmissionSettingController extends Controller
{
    private const DEFAULT_FORMAT = 'STU-{YYYY}-{SEQ:4}';

    public function edit(SettingService $settings)
    {
        $pattern = $settings->get('admission.id_format', self::DEFAULT_FORMAT);
        $example = IdPattern::example($pattern);

        return view('admission::settings.edit', compact('pattern', 'example'));
    }

    public function update(Request $request, SettingService $settings)
    {
        $data = $request->validate([
            'id_format' => ['required', 'string', 'max:60', 'regex:/\{SEQ:\d+\}/'],
        ], [
            'id_format.regex' => 'The pattern must include a {SEQ:N} sequence token, e.g. {SEQ:4}.',
        ]);

        $settings->set('admission.id_format', $data['id_format'], 'string', 'admission');

        return redirect()->route('admission.admin.settings.edit')->with('status', 'Settings saved.');
    }
}
