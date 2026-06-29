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
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('vehicle_type_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->enum('identification_type', ['nid', 'passport', 'driving_license'])->nullable()->comment('Type of ID provided');
            $table->string('identification_number')->unique()->nullable()->comment('Unique identification number');
            $table->string('identification_photo_front')->nullable()->comment('Front image of ID');
            $table->string('identification_photo_back')->nullable()->comment('Back image of ID');
            $table->string('address')->nullable();
            $table->enum('status', ['pending', 'approved', 'inactive'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_men');
    }
};
