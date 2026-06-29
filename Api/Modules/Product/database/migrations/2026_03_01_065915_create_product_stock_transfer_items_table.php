<?php

use App\Enums\StatusType;
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
        Schema::create('product_stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_stock_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            // Quantity
            $table->integer('qty_requested')->default(0); // requested (by branch or system)
            $table->integer('qty_dispatched')->nullable(); // shipped from source branch/hub
            $table->integer('qty_received')->nullable(); // confirmed received at destination branch
            // Cost tracking
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->text('note')->nullable();
            $table->enum('status', array_map(fn($enum) => $enum->value, StatusType::cases()))->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_transfer_items');
    }
};
