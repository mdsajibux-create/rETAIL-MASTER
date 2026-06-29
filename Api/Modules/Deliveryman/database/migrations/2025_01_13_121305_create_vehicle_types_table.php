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
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('capacity')->nullable()->comment('Load capacity in kilograms.');
            $table->string('speed_range')->nullable()->comment('Average speed range, e.g., 20-40 km/h.');
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid'])->nullable();
            $table->integer('max_distance')->nullable()->comment('Maximum distance per trip in kilometers.');
            $table->decimal('extra_charge', 8, 2)->nullable()->comment('Applicable if exceed max distance limit');
            $table->decimal('average_fuel_cost', 8, 2)->nullable()->comment('Fuel cost per trip.');
            $table->text('description')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
