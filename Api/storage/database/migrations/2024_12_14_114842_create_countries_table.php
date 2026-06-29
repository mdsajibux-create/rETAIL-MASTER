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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Country name (e.g., United States)
            $table->string('code'); // Country code (e.g., 'US')
            $table->string('dial_code')->nullable(); // Country dial code (e.g., '+1')
            $table->string('latitude')->nullable(); // Latitude of the country
            $table->string('longitude')->nullable(); // Longitude of the country
            $table->string('timezone')->nullable(); // Timezone of the country (e.g., 'America/New_York')
            $table->string('region')->nullable(); // Region or continent (e.g., 'North America')
            $table->string('languages')->nullable(); // Languages spoken in the country (e.g., 'English, Spanish')
            $table->integer('status')->comment('0=inactive, 1=active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
