<?php

namespace Modules\Foundation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Foundation\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // group, key, value, type
            ['general', 'school_name', 'EasySchool', 'string'],
            ['general', 'site_title', 'EasySchool ERP', 'string'],
            ['general', 'school_code', 'ES-001', 'string'],
            ['general', 'address', '', 'string'],
            ['general', 'phone', '', 'string'],
            ['general', 'email', '', 'string'],
            ['localization', 'currency_code', 'USD', 'string'],
            ['localization', 'currency_symbol', '$', 'string'],
            ['localization', 'currency_position', 'prefix', 'string'],
            ['localization', 'date_format', 'd M Y', 'string'],
            ['localization', 'timezone', 'UTC', 'string'],
            ['localization', 'language', 'en', 'string'],
            ['localization', 'week_start', 'Monday', 'string'],
            ['branding', 'logo', '', 'string'],
            ['branding', 'favicon', '', 'string'],
            ['branding', 'active_theme', 'default', 'string'],
            ['academic', 'with_guardian', '1', 'bool'],
            ['academic', 'result_type', 'mark', 'string'],
            ['fees', 'carry_forward_due_days', '60', 'int'],
        ];

        foreach ($defaults as [$group, $key, $value, $type]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => $group, 'value' => $value, 'type' => $type]
            );
        }
    }
}
