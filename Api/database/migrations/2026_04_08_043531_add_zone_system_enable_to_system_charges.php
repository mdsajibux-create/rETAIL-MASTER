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
        Schema::table('system_charges', function (Blueprint $table) {
            if (!Schema::hasColumn('system_charges', 'zone_system_enable')) {
                $table->boolean('zone_system_enable')->default(false)->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_charges', function (Blueprint $table) {
            $table->dropColumn('zone_system_enable');
        });
    }
};
