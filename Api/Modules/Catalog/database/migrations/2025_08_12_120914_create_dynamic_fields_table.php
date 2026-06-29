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
        Schema::create('dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('product_type');
            $table->enum('type', ['text', 'textarea', 'select', 'multiselect', 'number', 'date', 'time', 'color', 'boolean', 'checkbox', 'radio']);
            $table->boolean('is_required')->default(false);
            $table->enum('status', ['active', 'inactive','archived'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_fields');
    }
};
