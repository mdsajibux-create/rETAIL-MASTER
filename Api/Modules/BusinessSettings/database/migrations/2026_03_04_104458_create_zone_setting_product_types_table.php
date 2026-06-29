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
        Schema::create('zone_setting_product_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_setting_id');
            $table->unsignedBigInteger('product_type_id');
            $table->boolean('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_setting_product_types');
    }
};
