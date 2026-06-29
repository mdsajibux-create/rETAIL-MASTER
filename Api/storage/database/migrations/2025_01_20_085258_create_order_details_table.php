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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('behaviour')->nullable()->comment('service, digital, consumable, combo');
            $table->string('product_sku')->nullable();
            $table->json('variant_details')->nullable(); // product variants
            $table->unsignedBigInteger('product_campaign_id')->nullable();

            // Define precision and scale for decimal columns
            $table->decimal('base_price', 15, 2)->nullable(); // Original price of the product.
            $table->string('admin_discount_type')->nullable(); // percent/ fixed
            $table->decimal('admin_discount_rate', 15, 2)->nullable(); // 2% or 100-USD
            $table->decimal('admin_discount_amount', 15, 2)->nullable(); // 100
            $table->decimal('price', 15, 2)->nullable(); // after any discounts
            $table->decimal('quantity', 15, 2)->nullable();
            $table->decimal('line_total_price_with_qty', 15, 2)->nullable();
            $table->decimal('coupon_discount_amount', 15, 2)->default(0);
            $table->decimal('line_total_excluding_tax', 15, 2)->nullable();
            $table->decimal('tax_rate', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->decimal('total_tax_amount', 15, 2)->nullable(); // Total tax amount based on quantity
            $table->decimal('line_total_price', 15, 2)->nullable();

            // Admin commission amount and type
            $table->string('admin_commission_type')->nullable();
            $table->decimal('admin_commission_rate', 15, 2)->default(0.00);
            $table->decimal('admin_commission_amount', 15, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
