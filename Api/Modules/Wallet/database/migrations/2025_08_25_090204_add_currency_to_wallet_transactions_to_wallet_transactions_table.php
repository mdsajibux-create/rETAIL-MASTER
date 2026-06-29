<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'currency_code')) {
                $table->string('currency_code', 10)->nullable()->after('status');
            }
            if (!Schema::hasColumn('wallet_transactions', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 2)->default(1)->after('currency_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
            if (Schema::hasColumn('wallet_transactions', 'exchange_rate')) {
                $table->dropColumn('exchange_rate');
            }
        });
    }
};
