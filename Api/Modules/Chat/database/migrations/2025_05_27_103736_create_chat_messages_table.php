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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('receiver_chat_id');
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_type'); // 'customer', 'deliveryman', 'admin', 'store'
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_type'); // 'customer', 'deliveryman','admin', 'store'
            $table->longText('message')->nullable();
            $table->string('file')->nullable();
            $table->boolean('is_seen')->default(0)->comment('0: unseen, 1: seen');
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
