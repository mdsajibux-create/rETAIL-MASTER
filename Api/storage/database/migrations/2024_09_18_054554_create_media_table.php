<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->string('format')->nullable();
            $table->text('file_size')->nullable();
            $table->string('path')->nullable()->index();
            $table->string('alt_text')->nullable();
            $table->string('dimensions')->nullable()->index();
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
