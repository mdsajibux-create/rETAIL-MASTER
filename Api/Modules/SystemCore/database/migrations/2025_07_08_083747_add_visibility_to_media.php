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
        if (!Schema::hasColumn('media', 'visibility')) {
            Schema::table('media', function (Blueprint $table) {
                $table->enum('visibility', ['public', 'private', 'restricted'])->default('public')->after('user_type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
           $table->dropColumn('visibility');
        });
    }
};
