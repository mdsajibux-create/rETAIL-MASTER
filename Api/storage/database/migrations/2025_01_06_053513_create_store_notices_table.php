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
        Schema::create('store_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('message')->nullable();
            $table->enum('type', ['general', 'specific_store', 'specific_seller'])->default('general')->comment('general, specific_store, specific_seller');
            $table->enum('priority', ['low', 'medium', 'high'])->default('low')->comment('Priority: low, medium, high'); // Priority as a string
            $table->dateTime('active_date')->nullable()->comment('Start date of the notice'); // Active start date
            $table->dateTime('expire_date')->nullable()->comment('End date of the notice'); // Expiration date
            $table->integer('status')->default(1)->comment('0=inactive, 1=active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_notices');
    }
};
