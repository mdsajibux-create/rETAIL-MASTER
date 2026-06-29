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
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_master_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('type')->default('home')->comment('home, office, others'); // home, office, others.
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number');
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('road')->nullable();
            $table->string('house')->nullable();
            $table->string('floor')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
