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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->index('wallet_id');
            $table->string('transaction_ref')->nullable();
            $table->text('transaction_details')->nullable();
            $table->double('amount')->default(0);
            $table->string('type')->comment('credit or debit'); // Credit = add, Debit = deduct
            $table->string('purpose')->nullable(); // Purpose of the transaction (e.g., 'order', 'promotion', 'refund')
            $table->string('payment_gateway')->nullable();
            $table->string('payment_status')->nullable()->comment('pending , paid, failed');
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=success'); // 1 = success, 0 = pending, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
