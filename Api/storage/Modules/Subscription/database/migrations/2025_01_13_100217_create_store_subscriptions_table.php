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
        Schema::create('store_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('store_id');
            $table->bigInteger('subscription_id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->integer('validity');
            $table->double('price', 10, 2)->default(0);
            $table->boolean('pos_system')->default(false);
            $table->boolean('self_delivery')->default(false);
            $table->boolean('mobile_app')->default(false);
            $table->boolean('live_chat')->default(false);
            $table->integer('order_limit')->default(0);
            $table->integer('product_limit')->default(0);
            $table->integer('product_featured_limit')->default(0);
            $table->string('payment_gateway')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->string('manual_image')->nullable();
            $table->timestamp('expire_date')->nullable();
            $table->integer('status')->default(0)->comment('0=pending, 1=active, 2=cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_subscriptions');
    }
};
