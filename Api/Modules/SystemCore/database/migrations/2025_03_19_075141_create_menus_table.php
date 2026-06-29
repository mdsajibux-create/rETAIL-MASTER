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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->integer('position')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable(); // For nesting
            $table->string('parent_path')->nullable(); // For nesting
            $table->integer('menu_level')->nullable();           // Optional, similar to category_level
            $table->string('menu_path')->nullable();             // Optional, similar to parent_path
            $table->boolean('is_visible')->default(true);
            $table->integer('status')->default(1); // if status 1 editable and if 0  (seeder apply) not editable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
