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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('activity_scope')->nullable(); //system_level/store_level/customer_level/delivery_level/fitting_level,
            $table->text('email_verify_token')->nullable();
            $table->integer('email_verified')->default(0)->comment('0=unverified, 1=verified');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('password_changed_at')->nullable();
            $table->string('image')->nullable();
            $table->string('def_lang')->nullable();
            $table->boolean('activity_notification')->default(1);
            $table->string('firebase_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->unsignedBigInteger('store_owner')->nullable()->comment('1=store_owner');
            $table->unsignedBigInteger('store_seller_id')->nullable();
            $table->json('stores')->nullable();
            $table->integer('status')->default(0)->comment('0=Inactive,1=Active,2=Suspended');
            $table->rememberToken();
            $table->timestamp('deactivated_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
