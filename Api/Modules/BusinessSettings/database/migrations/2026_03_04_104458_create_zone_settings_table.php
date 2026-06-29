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
        Schema::create('zone_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->integer('delivery_time_per_km');
            $table->decimal('min_order_delivery_fee', 10, 2)->nullable();
            $table->string('delivery_charge_method')->nullable()->comment('fixed, per_km, range_wise');
            $table->decimal('out_of_area_delivery_charge', 10, 2)->nullable();
            $table->decimal('fixed_charge_amount', 10, 2)->nullable();
            $table->decimal('per_km_charge_amount', 10, 2)->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_settings');
    }
};
