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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('reviewable_id'); // ID of the reviewed entity (product or delivery man)
            $table->string('reviewable_type')->comment('product or delivery_man');
            $table->unsignedBigInteger('customer_id');
            $table->text('review');
            $table->decimal('rating', 3, 2)->comment('1-5 star rating');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('like_count')->default(0);
            $table->integer('dislike_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
