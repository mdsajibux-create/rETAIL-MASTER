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
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('dynamic_field_id')->nullable();
            $table->unsignedBigInteger('dynamic_field_value_id')->nullable()->comment('Used only for select/multiselect/checkbox/radio');
            $table->string('name')->nullable();
            $table->enum('type', ['text', 'textarea', 'select', 'multiselect', 'number', 'date', 'time', 'color', 'boolean', 'checkbox', 'radio']);
            $table->longtext('custom_value')->nullable()->comment('Free input value (text, textarea, number, date, time, boolean)');
            $table->string('status')->default(1)->comment('0: Inactive, 1: Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};
