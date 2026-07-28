<?php

namespace Modules\Foundation\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Foundation\Models\Setting;

/**
 * Central settings access — replaces the reference system's generalSetting() helper.
 * Values are cached as a single map and busted on write.
 */
class SettingService
{
    protected const CACHE_KEY = 'settings.map';

    /** @return array<string,mixed> key => typed value */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all()
                ->mapWithKeys(fn (Setting $s) => [$s->key => $s->typedValue()])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $type === 'json' ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value),
                'type'  => $type,
                'group' => $group,
            ]
        );

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
