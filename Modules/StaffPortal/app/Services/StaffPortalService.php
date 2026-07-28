<?php

namespace Modules\StaffPortal\Services;

use App\Models\User;
use Modules\HumanResource\Models\Staff;

/**
 * Resolves the logged-in user's own Staff record. This portal is reachable
 * by ANY authenticated user who has one (Teacher, Accountant, Librarian, ...
 * — not restricted to a single role), alongside their normal admin access,
 * not instead of it.
 */
class StaffPortalService
{
    public function staffFor(User $user): ?Staff
    {
        return Staff::where('user_id', $user->id)->first();
    }

    /** Aborts 403 if this user has no linked staff record at all. */
    public function assertStaff(User $user): Staff
    {
        $staff = $this->staffFor($user);
        abort_if($staff === null, 403, "You don't have a staff profile.");

        return $staff;
    }
}
