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
        if (!Schema::hasTable('product_types')) {
            Schema::create('product_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->boolean('charge_status')->default(false);
                $table->string('charge_name')->nullable();
                $table->decimal('charge_amount', 8, 2)->nullable();
                $table->enum('charge_type', ['fixed', 'percentage'])->nullable();
                $table->boolean('status')->default(0)->comment('0=Inactive, 1=Active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
