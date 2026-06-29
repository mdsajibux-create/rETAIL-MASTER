<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translatable_id');
            $table->string('translatable_type');
            $defaultLanguage = config('default_language', 'en');
            $table->string('language')->default($defaultLanguage);
            $table->string('key');
            $table->text('value');
            $table->timestamps();
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
    }
};
