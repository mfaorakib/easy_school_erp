<?php

namespace Modules\Fees\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeGroup;
use Modules\Fees\Models\FeeMaster;
use Modules\Fees\Models\FeeType;
use Modules\Foundation\Services\SettingService;

/** Starter fee setup so the module is usable on first run. */
class FeesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $group = FeeGroup::firstOrCreate(['name' => 'Monthly Fees'], ['description' => 'Recurring monthly charges']);
        $tuition = FeeType::firstOrCreate(['name' => 'Tuition'], ['code' => 'TUI']);
        FeeType::firstOrCreate(['name' => 'Transport'], ['code' => 'TRN']);

        FeeDiscount::firstOrCreate(['name' => 'Sibling'], ['amount_type' => 'percentage', 'amount' => 10]);
        FeeDiscount::firstOrCreate(['name' => 'Merit'], ['amount_type' => 'fixed', 'amount' => 500]);

        FeeMaster::firstOrCreate(
            ['fee_group_id' => $group->id, 'fee_type_id' => $tuition->id, 'class_id' => null],
            ['amount' => 1000, 'due_date' => now()->endOfMonth()->toDateString(), 'fine_type' => 'fixed', 'fine_value' => 50],
        );

        app(SettingService::class)->set('fees.receipt_format', 'RCT-{YYYY}-{SEQ:5}', 'string', 'fees');
    }
}
