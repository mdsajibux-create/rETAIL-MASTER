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
        Schema::create('delivery_man_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deliveryman_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->longText('review')->nullable()->comment('Customer’s feedback about the deliveryman');
            $table->unsignedTinyInteger('rating')->default(0)->comment('Rating from 1 to 5');
            $table->boolean('is_verified')->default(0)->comment('Indicates if the review has been verified by the admin');
            $table->timestamp('reviewed_at')->nullable()->comment('The time when the review was created');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_man_reviews');
    }
};
