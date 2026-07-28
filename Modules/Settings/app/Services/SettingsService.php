<?php

namespace Modules\Settings\Services;

use Modules\Foundation\Models\Setting;

/**
 * Read/write the shared key/value settings store (Foundation's `settings` table),
 * grouped by area. Array values (e.g. weekend days) are stored as type=json.
 */
class SettingsService
{
    /** All key => value pairs in a group. */
    public function group(string $group): array
    {
        return Setting::where('group', $group)->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->typedValue()])
            ->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $row = Setting::where('key', $key)->first();

        return $row ? $row->typedValue() : $default;
    }

    /** Upsert many key => value pairs into a group. */
    public function setMany(array $pairs, string $group): void
    {
        foreach ($pairs as $key => $value) {
            $isArray = is_array($value);
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'type'  => $isArray ? 'json' : 'string',
                    'value' => $isArray ? json_encode(array_values($value)) : $value,
                ],
            );
        }
    }

    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key);

        return is_array($value) ? $value : $default;
    }

    // --------------------------------------------------------- option lists

    /** A short, common timezone list for the localization form. */
    public function timezones(): array
    {
        return [
            'UTC', 'Asia/Dhaka', 'Asia/Kolkata', 'Asia/Karachi', 'Asia/Riyadh',
            'Asia/Dubai', 'Asia/Kuala_Lumpur', 'Asia/Jakarta', 'Europe/London',
            'America/New_York', 'America/Los_Angeles',
        ];
    }

    /** Supported date formats (PHP format => sample). */
    public function dateFormats(): array
    {
        return [
            'd M Y' => date('d M Y'),
            'd/m/Y' => date('d/m/Y'),
            'm/d/Y' => date('m/d/Y'),
            'Y-m-d' => date('Y-m-d'),
            'd-m-Y' => date('d-m-Y'),
        ];
    }

    public function weekDays(): array
    {
        return ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }
}
