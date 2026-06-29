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
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_color')->nullable();
            $table->text('description')->nullable();
            $table->string('description_color')->nullable();
            $table->string('background_color')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_text_color')->nullable();
            $table->string('button_hover_color')->nullable();
            $table->string('button_bg_color')->nullable();
            $table->string('button_url')->nullable();
            $table->string('timer_bg_color')->nullable();
            $table->string('timer_text_color')->nullable();
            $table->string('image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('discount_type')->nullable()->comment('percentage or amount');
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('special_price', 10, 2)->nullable()->comment('special price for product');
            $table->unsignedInteger('purchase_limit')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('status')->default(1)->comment('1: active, 0: inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};
