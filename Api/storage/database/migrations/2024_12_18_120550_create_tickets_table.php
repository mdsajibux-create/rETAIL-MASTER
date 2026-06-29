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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->index()->nullable();
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->unsignedBigInteger('store_id')->index()->nullable();
            $table->string('title');
            $table->string('subject');
            $table->string('priority')->nullable()->comment('low,high,medium,urgent'); // low,high, medium, urgent
            $table->integer('status')->default(1)->comment('0=inactive, 1=active');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
