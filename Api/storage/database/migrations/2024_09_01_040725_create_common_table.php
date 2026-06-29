<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Migrations\UniqueBaseMigration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translatable_id'); // ID of the related model
            $table->string('translatable_type'); // Model type (e.g., App\Models\Category)
            $defaultLanguage = config('bivamart.default_language', 'en');
            $table->string('language')->default($defaultLanguage);
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            //Unique Key
            //$table->unique(array('translatable_id', 'translatable_type','language','key'),'unique_translation');

            // Indexes for better performance
            $table->index(['translatable_id', 'translatable_type']);
            $table->index(['language', 'key']);
        });

        Schema::create('product_brand', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('brand_slug');
            $table->string('brand_logo')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('seller_relation_with_brand')->nullable();
            $table->timestamp('authorization_valid_from')->nullable();
            $table->timestamp('authorization_valid_to')->nullable();
            $table->integer('display_order')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('product_category', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_slug');
            $table->string('type');
            $table->string('category_name_paths')->nullable();
            $table->string('parent_path')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('category_level')->nullable();
            $table->boolean('is_featured')->default(1);
            $table->double('admin_commission_rate')->nullable();
            $table->string('category_thumb')->nullable();
            $table->string('category_banner')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('display_order')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('product_type')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('store_sellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->double('rating')->nullable();
            $table->integer('num_of_reviews')->nullable();
            $table->integer('num_of_sale')->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });


        Schema::create('store_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('name');
            // Google Maps-based zone management
            $table->geometry('coordinates', subtype: 'polygon')->nullable(); // Polygon boundary (Google Maps)
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();
            $table->boolean('status')->default(0)->comment('0=Inactive, 1=Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });


        Schema::create('store_area_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_area_id');
            $table->integer('delivery_time_per_km');
            $table->decimal('min_order_delivery_fee', 10, 2)->nullable();
            $table->string('delivery_charge_method')->nullable()->comment('fixed, per_km, range_wise');
            $table->decimal('out_of_area_delivery_charge', 10, 2)->nullable();
            $table->decimal('fixed_charge_amount', 10, 2)->nullable();
            $table->decimal('per_km_charge_amount', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('store_area_setting_store_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_area_setting_id');
            $table->unsignedBigInteger('store_type_id');
            $table->boolean('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->timestamps();
        });

        Schema::create('store_area_setting_range_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_area_setting_id');
            $table->decimal('min_km', 8, 2);
            $table->decimal('max_km', 8, 2);
            $table->decimal('charge_amount', 10, 2);
            $table->boolean('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('product_brand');
        Schema::dropIfExists('product_category');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('store_sellers');
        Schema::dropIfExists('store_areas');
        Schema::dropIfExists('store_types');
        Schema::dropIfExists('store_type_settings');
        Schema::dropIfExists('store_type_range_charges');
    }
};
