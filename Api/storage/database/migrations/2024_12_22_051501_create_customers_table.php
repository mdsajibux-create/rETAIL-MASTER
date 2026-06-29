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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->string('image')->nullable();
            $table->date('birth_day')->nullable();
            $table->string('gender')->nullable(); // male, female, others
            $table->string('def_lang')->nullable();
            $table->string('firebase_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->string('password');
            $table->timestamp('password_changed_at')->nullable();
            $table->text('email_verify_token')->nullable();
            $table->integer('email_verified')->default(0)->comment('0=unverified, 1=verified');
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('verified')->default(0)->comment('0: not verified, 1: verified');
            $table->string('verify_method')->default('email'); // Verification method ----> email, phone
            $table->boolean('activity_notification')->default(1);
            $table->boolean('marketing_email')->default(0);
            $table->boolean('marketing_sms')->default(0);
            $table->integer('status')->default(1)->comment('1: active, 0: inactive, 2: suspended');
            $table->timestamp('deactivated_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
