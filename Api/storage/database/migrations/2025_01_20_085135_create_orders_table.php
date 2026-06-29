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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_master_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('invoice_number')->unique()->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->string('order_type')->nullable()->comment('regular, pos'); // Order Type (Defines the nature of the order: regular, POS, etc.)
            $table->string('delivery_option')->nullable()->comment('home_delivery, parcel, takeaway'); // Delivery Type (Defines how the customer will receive the order: home delivery, pickup, etc.)
            $table->string('delivery_type')->nullable()->comment('standard, express, freight');  // Shipping Type (Defines how the goods are shipped: courier service, standard shipping, etc.)
            $table->string('delivery_time')->nullable();  // 10:00PM - 11:00AM
            $table->decimal('order_amount')->nullable();
            $table->decimal('order_amount_store_value')->nullable(); // Amount under this delivery package
            $table->decimal('order_amount_admin_commission')->nullable(); // Amount under this delivery package
            $table->decimal('product_discount_amount')->nullable();  // store wise product discount amount
            $table->decimal('flash_discount_amount_admin')->nullable(); // store wise dis.. discount amount
            $table->decimal('coupon_discount_amount_admin')->nullable(); // store wise discount amount
            $table->decimal('shipping_charge')->nullable(); // separate store wise shipping charge amount but total shipping amount in main order table
            $table->decimal('delivery_charge_admin')->nullable(); // If central Delivery then Value will be here
            $table->decimal('delivery_charge_admin_commission')->nullable(); // If Store delivery then admin will receive commission
            $table->string('order_additional_charge_name')->nullable();
            $table->decimal('order_additional_charge_amount')->nullable();
            $table->decimal('order_additional_charge_store_amount')->nullable();
            $table->decimal('order_admin_additional_charge_commission')->nullable();
            $table->boolean('is_reviewed')->nullable(); // customer review for order wise product reviews check
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('cancel_request_by')->nullable();
            $table->timestamp('cancel_request_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('delivery_completed_at')->nullable();
            $table->string('refund_status')->nullable()->comment('requested, processing, refunded, rejected');
            $table->string('status')->default('pending')->comment('pending, confirmed, processing , shipped, delivered, cancelled, on_hold');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
