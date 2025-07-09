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
        Schema::table('location_types', function (Blueprint $table) {
            $table->dropUnique('location_types_prefix_unique');
            $table->unique(['prefix', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_types', function (Blueprint $table) {
            $table->unique('prefix');
            $table->dropUnique('location_types_prefix_level_unique');
        });
    }
};
