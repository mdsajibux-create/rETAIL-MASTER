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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name')->default('default');
            $table->string('page_type')->default('dynamic_page');
            $table->string('is_dynamic')->default(0);
            $table->string('layout')->nullable()->default('default');
            $table->boolean('enable_builder')->default(false);
            $table->boolean('show_breadcrumb')->default(true);
            $table->string('page_class')->nullable();
            $table->unsignedBigInteger('page_parent')->nullable();
            $table->integer('page_order')->default(0);
            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->string('media')->nullable();
            $table->string('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('status')->default('draft')->comment('draft, publish');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
