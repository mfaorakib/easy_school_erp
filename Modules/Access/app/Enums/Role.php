<?php

namespace Modules\Access\Enums;

/**
 * Fixed system roles. IDs mirror the reference system's seeded role ids so business rules
 * that depend on them (e.g. student=2, teacher=4) stay faithful.
 * ("Parents" avoids the reserved word `parent`.)
 */
enum Role: string
{
    case SuperAdmin   = 'super-admin';
    case Student      = 'student';
    case Parents      = 'parent';
    case Teacher      = 'teacher';
    case Admin        = 'admin';
    case Accountant   = 'accountant';
    case Receptionist = 'receptionist';
    case Librarian    = 'librarian';
    case Driver       = 'driver';

    public function id(): int
    {
        return match ($this) {
            self::SuperAdmin   => 1,
            self::Student      => 2,
            self::Parents      => 3,
            self::Teacher      => 4,
            self::Admin        => 5,
            self::Accountant   => 6,
            self::Receptionist => 7,
            self::Librarian    => 8,
            self::Driver       => 9,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin   => 'Super Admin',
            self::Student      => 'Student',
            self::Parents      => 'Parent',
            self::Teacher      => 'Teacher',
            self::Admin        => 'Admin',
            self::Accountant   => 'Accountant',
            self::Receptionist => 'Receptionist',
            self::Librarian    => 'Librarian',
            self::Driver       => 'Driver',
        };
    }
}
