<?php

use App\Enums\Behaviour;
use App\Enums\StatusType;
use App\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->enum('type', array_map(fn($enum) => $enum->value, ProductType::cases()))->nullable();
            $table->enum('behaviour', array_map(fn($enum) => $enum->value, Behaviour::cases()))->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('gallery_images')->nullable();
            $table->string('warranty')->nullable();
            $table->string('class')->nullable();
            $table->string('return_in_days')->nullable();
            $table->string('return_text')->nullable();
            $table->string('allow_change_in_mind')->nullable();
            // Delivery config
            $table->integer('cash_on_delivery')->nullable();
            $table->string('delivery_time_min')->nullable();
            $table->string('delivery_time_max')->nullable();
            $table->string('delivery_time_text')->nullable();
            // Purchase limits
            $table->integer('max_cart_qty')->nullable();
            $table->integer('order_count')->nullable();
            $table->integer('views')->default(0);
            $table->enum('status', array_map(fn($enum) => $enum->value, StatusType::cases()))->default('pending');
            // specific features
            $table->timestamp('available_time_starts')->nullable();
            $table->timestamp('available_time_ends')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_featured')->default(false);
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
