<?php

namespace Modules\Foundation\Support;

/**
 * Formats a configurable unique-ID pattern (e.g. admission IDs) into a concrete
 * string. Pure templating — no database access — so it can be reused wherever a
 * pattern + a year + a sequence number need to become an ID.
 *
 * Tokens: {YYYY} full year, {YY} 2-digit year, {SEQ:N} the sequence
 * zero-padded to N digits (e.g. {SEQ:4} → 0007).
 */
class IdPattern
{
    public static function format(string $pattern, int $year, int $seq): string
    {
        $value = str_replace(
            ['{YYYY}', '{YY}'],
            [(string) $year, substr((string) $year, -2)],
            $pattern
        );

        return preg_replace_callback('/\{SEQ:(\d+)\}/', function ($m) use ($seq) {
            return str_pad((string) $seq, (int) $m[1], '0', STR_PAD_LEFT);
        }, $value);
    }

    /** A sample string for a given pattern, for live-preview UIs. */
    public static function example(string $pattern): string
    {
        return static::format($pattern, (int) date('Y'), 7);
    }
}
