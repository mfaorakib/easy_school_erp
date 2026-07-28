<?php

namespace Modules\Leave\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HumanResource\Models\Staff;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Models\Shift;
use Modules\Leave\Services\LeaveService;

class LeaveDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Casual Leave', 10], ['Sick Leave', 14], ['Annual Leave', 20], ['Maternity Leave', 90]] as [$name, $days]) {
            LeaveType::firstOrCreate(['name' => $name], ['days_allowed' => $days]);
        }

        $morning = Shift::firstOrCreate(['name' => 'Morning'], ['start_time' => '08:00', 'end_time' => '14:00']);
        Shift::firstOrCreate(['name' => 'Day'], ['start_time' => '09:00', 'end_time' => '16:00']);

        $svc = app(LeaveService::class);
        $staff = Staff::where('is_active', true)->orderBy('id')->first();
        if ($staff) {
            $svc->assignShift($staff->id, $morning->id);
            $svc->apply([
                'staff_id'      => $staff->id,
                'leave_type_id' => LeaveType::where('name', 'Casual Leave')->value('id'),
                'from_date'     => now()->addDays(5)->toDateString(),
                'to_date'       => now()->addDays(6)->toDateString(),
                'reason'        => 'Family event.',
            ]);
        }
    }
}
