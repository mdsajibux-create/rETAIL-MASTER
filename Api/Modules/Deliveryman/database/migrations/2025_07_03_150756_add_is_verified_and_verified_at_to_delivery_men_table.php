<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('delivery_men', 'is_verified') || !Schema::hasColumn('delivery_men', 'verified_at')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_men', 'is_verified')) {
                    $table->integer('is_verified')->default(0)->after('address')->comment('0 - pending, 1 - verified, 2 - rejected');
                }

                if (!Schema::hasColumn('delivery_men', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('is_verified');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('delivery_men', 'is_verified') || Schema::hasColumn('delivery_men', 'verified_at')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                $table->dropColumn(['is_verified', 'verified_at']);
            });
        }
    }
};
