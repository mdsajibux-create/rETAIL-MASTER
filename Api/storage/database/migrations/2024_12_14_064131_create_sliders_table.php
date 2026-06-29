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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['web', 'mobile'])->default('web');
            $table->string('title');
            $table->string('title_color')->nullable();
            $table->string('sub_title')->nullable();
            $table->string('sub_title_color')->nullable();
            $table->longText('description')->nullable();
            $table->string('description_color')->nullable();
            $table->string('image')->nullable();
            $table->string('bg_image')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_text_color')->nullable();
            $table->string('button_bg_color')->nullable();
            $table->string('button_hover_color')->nullable();
            $table->string('button_url')->nullable();
            $table->string('redirect_url')->nullable();
            $table->integer('order')->nullable();
            $table->integer('status')->default(0)->comment('0 - Inactive, 1 - Active');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
