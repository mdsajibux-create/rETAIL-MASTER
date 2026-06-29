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
        Schema::create('order_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable(); // Reference ID from List Table
            $table->unsignedBigInteger('collected_by')->nullable(); // who collected the activity value from the admin end
            $table->string('activity_from')->nullable(); //Admin/Store/Customer/Deliveryman
            $table->string('activity_type')->nullable(); // e.g. cash_collection, cash_deposit etc.
            $table->string('reference')->nullable(); // Text from Reference Table or Fixed text
            $table->string('activity_value')->nullable(); // Value of Given Activity, For Cancel reason there will be no Value, for feedback there may decimal value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_activities');
    }
};
