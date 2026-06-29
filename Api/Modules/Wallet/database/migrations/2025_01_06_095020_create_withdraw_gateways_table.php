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
        Schema::create('withdraw_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Method name (e.g., "PayPal", "Bank Transfer")
            $table->json('fields')->nullable()->comment('stored as JSON');
            $table->integer('status')->default(1)->comment('1=active, 0=inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_gateways');
    }
};
