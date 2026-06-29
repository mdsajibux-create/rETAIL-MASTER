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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Subscription package name
            $table->string('type')->nullable(); //trial, weekly, monthly yearly
            $table->integer('validity'); // Validity period in days
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->double('price', 10, 2)->default(0);
            $table->boolean('pos_system')->default(false);
            $table->boolean('self_delivery')->default(false);
            $table->boolean('mobile_app')->default(false);
            $table->boolean('live_chat')->default(false);
            $table->integer('order_limit')->default(0);
            $table->integer('product_limit')->default(0);
            $table->integer('product_featured_limit')->default(0);
            $table->integer('status')->default(0)->comment('0=inactive, 1=active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
