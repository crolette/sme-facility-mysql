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
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();
        });
    }
};
