<?php

namespace Modules\Wallet\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Wallet\Services\WalletService;

class WalletDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(WalletService::class);

        $admin = User::where('username', 'admin')->first();
        if ($admin) {
            $wallet = $service->ensureWallet($admin->id);
            if ($wallet->transactions()->count() === 0) {
                $service->deposit($wallet, 500, 'Opening balance');
            }
        }
    }
}
