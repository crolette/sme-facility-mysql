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
        Schema::table('interventions', function (Blueprint $table) {
            // $table->foreignId('maintainable_id')->constrained()->cascadeOnDelete()->nullable()->change();
            $table->unsignedBigInteger('maintainable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->unsignedBigInteger('maintainable_id')->nullable(true)->change();
            // $table->foreignId('maintainable_id')->constrained()->cascadeOnDelete();
        });
    }
};
