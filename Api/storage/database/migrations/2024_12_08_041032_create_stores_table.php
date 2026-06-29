<?php

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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('store_seller_id')->nullable();
            $table->enum('store_type', array_map(fn($enum) => $enum->value, ProductType::cases()))->nullable(); //medicine/ furniture/ DOOR/ FOOD/ GROCERY
            $table->decimal('tax', 5, 2)->default(0);
            $table->string('tax_number')->nullable();
            $table->string('subscription_type', 50)->nullable();
            $table->string('admin_commission_type')->nullable(); // percent or amount
            $table->decimal('admin_commission_amount', 10, 2)->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_featured')->nullable()->default(false);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->decimal('delivery_charge', 10, 2)->nullable();
            $table->string('delivery_time', 50)->nullable();
            $table->boolean('delivery_self_system')->nullable()->default(false);
            $table->boolean('delivery_take_away')->nullable()->default(false);
            $table->integer('order_minimum')->nullable()->default(0);
            $table->integer('veg_status')->nullable()->default(0)->comment('0 = Non-Vegetarian, 1 = Vegetarian');
            $table->string('off_day', 50)->nullable(); // e.g., 'Sunday'
            $table->integer('enable_saling')->nullable()->default(0)->comment('0 = Sales disabled, 1 = Sales enabled');
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->integer('status')->nullable()->default(0)->comment('0 = Pending, 1 = Active, 2 = Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
