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
        Schema::create('coupon_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('coupon_code')->unique();
            $table->string('discount_type')->comment('percentage or amount');
            $table->double('discount');
            $table->double('min_order_value')->nullable();
            $table->double('max_discount')->nullable();
            $table->unsignedInteger('usage_limit')->nullable()->comment('Global usage limit for the coupon');
            $table->unsignedInteger('usage_count')->default(0)->comment('Number of times the coupon has been used globally');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('status')->default(1)->comment('0=inactive, 1=active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_lines');
    }
};
