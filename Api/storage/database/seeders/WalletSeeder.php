<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletTransaction;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Wallet::create([
            "owner_id" => 1,
            "owner_type" => 'Modules\Branch\app\Models\Branch',
            "balance" => 100,
            "status" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

        WalletTransaction::create([
            "wallet_id" => 2,
            "transaction_ref" => '123456789',
            "transaction_details" => 'test info',
            "amount" => 100,
            "type" => 'credit',
            "purpose" => 'check amount',
            "status" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

        Wallet::create([
            "owner_id" => 2,
            "owner_type" => 'Modules\Branch\app\Models\Branch',
            "balance" => 100,
            "status" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

        WalletTransaction::create([
            "wallet_id" => 2,
            "transaction_ref" => '123456789',
            "transaction_details" => 'test info',
            "amount" => 100,
            "type" => 'credit',
            "purpose" => 'check amount',
            "status" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);
        Wallet::create([
            "owner_id" => 6,
            "owner_type" => 'App\Models\User',
            "balance" => 0.00,
            "status" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

    }
}
