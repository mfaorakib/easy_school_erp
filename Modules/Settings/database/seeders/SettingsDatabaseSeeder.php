<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\Holiday;
use Modules\Settings\Models\PaymentMethod;
use Modules\Settings\Services\SettingsService;

class SettingsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SettingsService::class);

        // Fill in the general keys Foundation seeds blank (reuses the same keys).
        $settings->setMany([
            'school_name'     => 'EasySchool',
            'address'         => '12 Learning Avenue, Dhaka 1207',
            'phone'           => '+880 1700-000000',
            'email'           => 'info@easyschool.test',
            'currency_code'   => 'BDT',
            'currency_symbol' => '৳',
        ], 'general');

        $settings->setMany([
            'timezone'         => 'Asia/Dhaka',
            'date_format'      => 'd M Y',
            'default_language' => 'en',
            'weekend_days'     => ['friday'],
        ], 'localization');

        $settings->setMany([
            'mail_host' => 'smtp.mailtrap.io', 'mail_port' => '2525',
            'mail_username' => '', 'mail_password' => '', 'mail_encryption' => 'tls',
            'mail_from_address' => 'info@easyschool.test', 'mail_from_name' => 'EasySchool',
        ], 'email');

        $settings->setMany([
            'sms_provider' => 'none', 'sms_api_key' => '', 'sms_sender_id' => 'EasySchool',
        ], 'sms');

        $settings->setMany([
            'admin_primary_color' => '#4f46e5', 'admin_theme' => 'light',
        ], 'appearance');

        // Manual channels need no config; the 3 real gateways ship with obviously-fake
        // placeholder credentials — a school replaces these with their own before going
        // live (see Settings → Payment Methods).
        $methods = [
            ['name' => 'Cash', 'driver' => PaymentMethod::DRIVER_MANUAL],
            ['name' => 'Bank Transfer', 'driver' => PaymentMethod::DRIVER_MANUAL],
            ['name' => 'Rocket', 'driver' => PaymentMethod::DRIVER_MANUAL],
            ['name' => 'Cheque', 'driver' => PaymentMethod::DRIVER_MANUAL],
            ['name' => 'Card (Stripe)', 'driver' => PaymentMethod::DRIVER_STRIPE, 'config' => [
                'secret_key' => 'sk_test_placeholder', 'publishable_key' => 'pk_test_placeholder',
                'webhook_secret' => '', 'currency' => 'usd',
            ]],
            ['name' => 'bKash', 'driver' => PaymentMethod::DRIVER_BKASH, 'config' => [
                'app_key' => 'placeholder_app_key', 'app_secret' => 'placeholder_app_secret',
                'username' => 'placeholder_username', 'password' => 'placeholder_password', 'sandbox' => true,
            ]],
            ['name' => 'Nagad', 'driver' => PaymentMethod::DRIVER_NAGAD, 'config' => [
                'merchant_id' => 'placeholder_merchant_id',
                'merchant_private_key' => '', 'pg_public_key' => '', 'sandbox' => true,
            ]],
        ];
        foreach ($methods as $i => $m) {
            PaymentMethod::firstOrCreate(
                ['name' => $m['name']],
                ['driver' => $m['driver'], 'config' => $m['config'] ?? null, 'position' => $i + 1],
            );
        }

        Holiday::firstOrCreate(
            ['title' => 'Victory Day'],
            ['from_date' => now()->setDate(now()->year, 12, 16)->toDateString(),
             'to_date'   => now()->setDate(now()->year, 12, 16)->toDateString(),
             'description' => 'National holiday.'],
        );
    }
}
