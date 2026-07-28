<?php

namespace Modules\Attendance\Enums;

/**
 * Attendance states. Stored as single-letter codes (compact, report-friendly),
 * matching the reference system's P/L/A/H/O convention.
 */
enum AttendanceStatus: string
{
    case Present  = 'P';
    case Late     = 'L';
    case Absent   = 'A';
    case HalfDay  = 'F'; // reference codes: Half-Day = F, Holiday = H
    case Holiday  = 'H';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Late    => 'Late',
            self::Absent  => 'Absent',
            self::HalfDay => 'Half Day',
            self::Holiday => 'Holiday',
        };
    }

    /** Does this state count as "present" for percentage calculations? */
    public function isPresent(): bool
    {
        return in_array($this, [self::Present, self::Late, self::HalfDay], true);
    }

    /** States an admin can pick when marking a roster (Holiday is set day-wide). */
    public static function markable(): array
    {
        return [self::Present, self::Late, self::Absent, self::HalfDay];
    }
}
