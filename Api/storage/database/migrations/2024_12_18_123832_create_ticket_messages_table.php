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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index(); // The ticket this message belongs to
            $table->unsignedBigInteger('sender_id')->nullable()->index(); // The user (sender) who sent the message
            $table->unsignedBigInteger('receiver_id')->nullable()->index(); // The user (receiver) who receives the message
            $table->string('sender_role')->nullable(); // The role of the sender (admin, seller, customer, etc.)
            $table->string('receiver_role')->nullable(); // The role of the receiver (admin, seller, customer, etc.)
            $table->longText('message')->nullable(); // The actual message content
            $table->string('file')->nullable(); //file type like: jpg,png,jpeg, webp, zip
            $table->boolean('is_read')->default(false); // Whether the message has been read
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
