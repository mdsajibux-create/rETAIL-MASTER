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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('who created the banner');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('title');
            $table->string('title_color')->nullable();
            $table->text('description')->nullable();
            $table->string('description_color')->nullable();
            $table->string('background_image')->nullable();
            $table->string('background_color')->nullable();
            $table->string('thumbnail_image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_text_color')->nullable();
            $table->string('button_hover_color')->nullable();
            $table->string('button_color')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('location')->default('home_page')->comment('the location of the banner Home Page or Store Page');
            $table->string('type')->nullable()->comment('Ex: Banner-1, Banner-2, Banner-3');
            $table->integer('status')->default(0)->comment('0=inactive, 1=active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
