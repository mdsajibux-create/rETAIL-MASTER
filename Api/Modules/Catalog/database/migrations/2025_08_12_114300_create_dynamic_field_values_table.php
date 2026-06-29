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
        Schema::create('dynamic_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dynamic_field_id');
            $table->text('value'); // store input value (JSON if multiselect)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_field_values');
    }
};
