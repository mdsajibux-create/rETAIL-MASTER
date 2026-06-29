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
        Schema::create('wallet_withdrawals_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->index();
            $table->unsignedBigInteger('owner_id')->index();
            $table->string('owner_type')->nullable()->comment('store or deliveryman or customer');
            $table->unsignedBigInteger('withdraw_gateway_id')->index();
            $table->string('gateway_name')->nullable();
            $table->decimal('amount', 15, 2); // Use decimal
            $table->decimal('fee', 15, 2)->default(0.00); // Fee applied to the withdrawal
            $table->json('gateways_options')->nullable();
            $table->longText('details')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index(); // Approved by reference
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('pending')->comment('pending, approved, rejected');
            $table->text('reject_reason')->nullable();
            $table->string('attachment')->nullable();
            $table->index(['owner_id', 'owner_type', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_withdrawals_transactions');
    }
};
