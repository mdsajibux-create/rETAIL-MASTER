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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('variant_slug')->nullable();
            $table->string('sku')->nullable(); // seller SKU
            $table->decimal('pack_quantity', 15, 2)->nullable();
            $table->decimal('weight_major', 15, 2)->nullable();
            $table->decimal('weight_gross', 15, 2)->nullable();
            $table->decimal('weight_net', 15, 2)->nullable();
            $table->json('attributes')->nullable();
            $table->decimal('price', 15, 2)->nullable(); // Base price for the variant
            $table->decimal('special_price', 15, 2)->nullable(); // Special discounted price
            $table->integer('stock_quantity')->default(0);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('length', 15, 2)->nullable();
            $table->decimal('width', 15, 2)->nullable();
            $table->decimal('height', 15, 2)->nullable();
            $table->string('image')->nullable(); //[{"sliding_image":"xyx.jpg","position":1},{"sliding_image":"abc.jpg","position":2}]
            $table->integer('order_count')->default(0);
            $table->integer('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->softDeletes();
            $table->timestamps();
            // indexes search performance
            $table->index('product_id');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
