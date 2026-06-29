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
        Schema::create('system_charges', function (Blueprint $table) {
            $table->id();
            $table->decimal('order_shipping_charge', 8, 2)->nullable();
            $table->string('order_confirmation_by')->default('deliveryman');
            $table->boolean('order_include_tax_amount')->default(false);
            $table->decimal('order_tax', 8, 2)->nullable();
             // Additional Charge Settings
            $table->boolean('order_additional_charge_enable_disable')->default(false);
            $table->string('order_additional_charge_name')->nullable();
            $table->decimal('order_additional_charge_amount', 8, 2)->nullable();
            // Deliveryman earning system
            $table->enum('deliveryman_earning_type', ['salary', 'commission'])->default('salary');
            $table->enum('deliveryman_commission_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('deliveryman_commission_value', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_charges');
    }
};
