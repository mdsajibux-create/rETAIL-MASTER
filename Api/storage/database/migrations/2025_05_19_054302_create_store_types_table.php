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
        if (!Schema::hasTable('store_types')) {
            Schema::create('store_types', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Store type name (e.g., Grocery, Pharmacy)
                $table->string('type');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->bigInteger('total_stores')->default(0);
                $table->boolean('additional_charge_enable_disable')->default(false);
                $table->string('additional_charge_name')->nullable();
                $table->decimal('additional_charge_amount', 8, 2)->nullable();
                $table->enum('additional_charge_type', ['fixed', 'percentage'])->nullable();
                $table->decimal('additional_charge_commission', 8, 2)->nullable();
                $table->boolean('status')->default(0)->comment('0=Inactive, 1=Active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_types');
    }
};
