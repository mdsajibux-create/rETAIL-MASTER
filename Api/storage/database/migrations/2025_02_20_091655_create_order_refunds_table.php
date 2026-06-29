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
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // Foreign key to the order
            $table->unsignedBigInteger('customer_id'); // Foreign key to the customer
            $table->unsignedBigInteger('store_id'); // Foreign key to the seller
            $table->unsignedBigInteger('order_refund_reason_id'); // Foreign key to the refund reason
            $table->text('customer_note')->nullable(); // Optional note from the customer
            $table->string('file')->nullable(); //file type like: jpg,png,jpeg, webp, zip
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2); // Refund amount
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
