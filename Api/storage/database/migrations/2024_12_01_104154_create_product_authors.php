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
        Schema::create('product_authors', function (Blueprint $table) {
            $table->id();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('bio')->nullable();
            $table->date('born_date')->nullable();
            $table->date('death_date')->nullable();
            $table->integer('status')->default('0')->comment('1 = active, 0 = inactive'); //1. Active or empty, 2. Inactive
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_authors');
    }
};
