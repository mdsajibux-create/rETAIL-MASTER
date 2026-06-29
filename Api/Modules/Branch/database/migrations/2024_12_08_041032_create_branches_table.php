<?php

use App\Enums\BranchType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->enum('type', array_map(fn($enum) => $enum->value, BranchType::cases()))->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // delivery
            $table->decimal('delivery_charge', 10, 2)->nullable();
            $table->string('delivery_time', 50)->nullable();
            $table->boolean('delivery_self_system')->nullable()->default(false);
            $table->boolean('delivery_take_away')->nullable()->default(false);
            // Working Hours
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->string('off_day', 50)->nullable();
            $table->integer('status')->nullable()->default(0)->comment('0 = Pending, 1 = Active, 2 = Inactive, 3= Rejected');
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
        Schema::dropIfExists('branches');
    }
};
