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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->boolean('available_to_seller')->default(false);
            $table->string('status');
            $table->integer('position');
            $table->timestamps();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
        Schema::table('permissions', function (Blueprint $table) {
            $table->removeColumn('module');
        });
    }
};
