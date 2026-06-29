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
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->after('zone_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
            $table->unsignedBigInteger('area_id')->nullable()->after('city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['state_id', 'city_id', 'area_id']);
        });
    }
};
