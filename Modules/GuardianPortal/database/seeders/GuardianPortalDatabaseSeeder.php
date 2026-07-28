<?php

namespace Modules\GuardianPortal\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\Section;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Services\AdmissionService;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeMaster;
use Modules\Fees\Services\FeeService;

/**
 * Confirms one of Admission's demo pending applications into a real
 * guardian+student, assigns the demo tuition fee, attaches a demo scholarship
 * discount, and records one payment — so the Guardian Portal has something
 * real to show without any manual setup. Runs last (needs Admission + Fees
 * demo data already seeded).
 */
class GuardianPortalDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $application = AdmissionApplication::where('status', AdmissionApplication::STATUS_PENDING)->first();
        $section = Section::first();
        $admin = User::where('email', 'admin@easyschool.test')->first();

        if (! $application || ! $section || ! $admin) {
            return;
        }

        $application = app(AdmissionService::class)->confirm($application, ['section_id' => $section->id], $admin);
        $student = $application->student;
        if (! $student) {
            return;
        }

        // Give the guardian a memorable demo password (the admission flow's
        // default is a hardcoded '123456', which already applies — this just
        // documents it here for anyone demoing the portal).
        $fees = app(FeeService::class);

        $master = FeeMaster::first();
        if ($master) {
            $assignment = $fees->assignToStudent($master, $student->id);

            $discount = FeeDiscount::where('name', 'Sibling')->first();
            if ($discount) {
                $fees->attachDiscount($assignment, $discount, 'Younger sibling already enrolled — standard sibling discount.', $admin);
            }

            // Partially pay it so the portal demonstrates both "due" and "paid" states.
            $balance = $fees->balance($assignment->fresh());
            if ($balance['due'] > 0) {
                $fees->collect($assignment, [
                    'amount'      => round($balance['due'] / 2, 2),
                    'method'      => 'Cash',
                    'reference'   => 'DEMO-CASH-001',
                    'note'        => 'Partial payment collected at the front desk.',
                    'received_by' => $admin->id,
                ]);
            }
        }
    }
}
