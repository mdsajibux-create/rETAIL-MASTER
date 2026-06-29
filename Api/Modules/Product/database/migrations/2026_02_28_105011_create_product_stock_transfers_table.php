<?php

use App\Enums\ProductStockTransferType;
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
        Schema::create('product_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // MOV-2025-00001
            $table->enum('type', array_map(fn($enum) => $enum->value, ProductStockTransferType::cases()))->nullable();
            // branch (from → to)
            $table->unsignedBigInteger('from_branch_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();
            // Reference links
            $table->unsignedBigInteger('order_id')->nullable();         // sale movement এ
            $table->unsignedBigInteger('purchase_order_id')->nullable();// purchase movement এ
            $table->string('supplier_reference')->nullable();           // supplier invoice no
            // People
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('dispatched_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            // Notes
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('status', array_map(fn($enum) => $enum->value, StatusType::cases()))->nullable();
            // Timestamps
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('expected_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_transfers');
    }
};
