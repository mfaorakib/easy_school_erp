<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounting\Models\AccountHead;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\BankAccount;
use Modules\Accounting\Services\AccountingService;

class AccountingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tuition Fees', 'Donation', 'Other Income'] as $name) {
            AccountHead::firstOrCreate(['name' => $name, 'type' => 'income']);
        }
        foreach (['Salaries', 'Utilities', 'Maintenance', 'Supplies'] as $name) {
            AccountHead::firstOrCreate(['name' => $name, 'type' => 'expense']);
        }

        $main = BankAccount::firstOrCreate(
            ['bank_name' => 'City Bank', 'account_number' => '1001-2002-3003'],
            ['account_name' => 'EasySchool Operating', 'account_type' => 'Current', 'opening_balance' => 50000],
        );
        BankAccount::firstOrCreate(
            ['bank_name' => 'Cash in Hand'],
            ['account_name' => 'Petty Cash', 'account_type' => 'Cash', 'opening_balance' => 5000],
        );

        if (AccountTransaction::count() === 0) {
            $svc = app(AccountingService::class);
            $donation = AccountHead::income()->where('name', 'Donation')->first();
            $utilities = AccountHead::expense()->where('name', 'Utilities')->first();

            $svc->record([
                'type' => 'income', 'account_head_id' => $donation->id, 'bank_account_id' => $main->id,
                'title' => 'Alumni donation', 'amount' => 25000, 'payment_method' => 'bank',
            ]);
            $svc->record([
                'type' => 'expense', 'account_head_id' => $utilities->id, 'bank_account_id' => $main->id,
                'title' => 'Electricity bill — July', 'amount' => 8000, 'payment_method' => 'bank',
            ]);
        }
    }
}
