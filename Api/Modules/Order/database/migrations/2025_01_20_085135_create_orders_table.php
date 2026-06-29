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
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('invoice_number')->unique()->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->string('order_type')->nullable()->comment('regular, pos');
            $table->string('delivery_option')->nullable()->comment('home_delivery, parcel, takeaway, in_store');
            $table->string('delivery_type')->nullable()->comment('standard, express, freight,immediate');
            $table->string('delivery_time')->nullable();  // 10:00PM - 11:00AM
            $table->decimal('order_amount')->nullable();
            $table->decimal('product_discount_amount')->nullable();
            $table->decimal('flash_discount_amount')->nullable();
            $table->decimal('coupon_discount_amount')->nullable();
            $table->decimal('shipping_charge')->nullable(); // separate store wise shipping charge amount but total shipping amount in main order table
            $table->decimal('delivery_charge')->nullable(); // If central Delivery then Value will be here
            $table->string('additional_charge_name')->nullable();
            $table->decimal('additional_charge_amount')->nullable();
            $table->string('payment_gateway')->nullable()->comment('cash,card,wallet,cash_on_delivery,others');
            $table->string('payment_status')->nullable()->comment('pending, partially_paid, paid, cancelled, failed');
            $table->string('transaction_ref')->nullable();
            $table->string('transaction_details')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('order_notes')->nullable();
            $table->boolean('is_reviewed')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('cancel_request_by')->nullable();
            $table->timestamp('cancel_request_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('delivery_completed_at')->nullable();
            $table->enum('refund_status',['requested', 'processing', 'refunded', 'rejected'])
                ->nullable()
                ->comment('requested, processing, refunded, rejected');
            $table->enum('status',['pending', 'confirmed', 'processing', 'pickup', 'shipped', 'delivered', 'cancelled', 'on_hold'])
                ->default('pending')
                ->comment('pending, confirmed, processing, pickup, shipped, delivered, cancelled, on_hold');
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
