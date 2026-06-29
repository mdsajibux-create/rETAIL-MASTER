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
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the state
            $table->unsignedBigInteger('country_id');
            $table->string('timezone')->nullable(); // Timezone of the country (e.g., 'America/New_York')
            $table->tinyInteger('status')->comment('0=inactive, 1=active');
            $table->index('country_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
