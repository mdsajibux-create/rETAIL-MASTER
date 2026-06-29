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
        Schema::create('product_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('stock_id');
            $table->enum('type', ['opening', 'stock_in', 'stock_out', 'transfer_in', 'transfer_out', 'adjustment', 'damaged', 'return']);
            $table->integer('qty_before');
            $table->integer('qty_changed');
            $table->integer('qty_after');
            $table->integer('qty_damaged')->default(0);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('reason')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_logs');
    }
};
