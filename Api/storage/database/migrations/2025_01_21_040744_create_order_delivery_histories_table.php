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
        Schema::create('order_delivery_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('deliveryman_id');
            $table->text('reason')->nullable()->comment('Reason for ignoring or cancelling delivery');
            $table->enum('status', ['accepted', 'ignored', 'delivered', 'cancelled'])->comment('accepted, ignored, delivered, cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_histories');
    }
};
