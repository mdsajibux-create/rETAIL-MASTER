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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();

            // quantities (unsigned — cannot be negative)
            $table->unsignedInteger('qty')->default(0);    // available in branch
            $table->unsignedInteger('qty_reserved')->default(0);   // allocated to orders
            $table->unsignedInteger('qty_incoming')->default(0);   // incoming from transfers / POs
            $table->unsignedInteger('qty_damaged')->default(0);    // damaged / write-off

            // replenishment
            $table->integer('reorder_point')->default(5);         // stock alert
            $table->integer('reorder_qty')->default(0);      // order qty minimum

            // pricing (location-specific overrides)
            $table->decimal('cost_price', 14, 4)->nullable();      // more precision for cost
            $table->decimal('selling_price', 14, 4)->nullable();   // branch-level override
            $table->decimal('sale_price', 14, 4)->nullable();      // discount / campaign price

            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->unique(
                ['branch_id', 'product_id', 'variant_id'],
                'uq_stock_branch_product_variant'
            );

            $table->timestamp('last_counted_at')->nullable();     // last stock count
            $table->timestamp('last_restocked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
