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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');  // Polymorphic ID (Customer ID or Store ID or Deliveryman ID)
            $table->string('owner_type')->comment('store or deliveryman or customer');
            $table->double('balance')->default(0);
            $table->decimal('earnings', 15, 2)->default(0);
            $table->decimal('withdrawn', 15, 2)->default(0);
            $table->decimal('refunds', 15, 2)->default(0);
            $table->tinyInteger('status')->default(1)->comment('0=inactive, 1=active');
            $table->timestamps();
            $table->index(['owner_id', 'owner_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
