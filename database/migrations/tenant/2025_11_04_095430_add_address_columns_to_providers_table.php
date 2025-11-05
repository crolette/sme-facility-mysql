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
        Schema::table('providers', function (Blueprint $table) {
            $table->string('street', 100);
            $table->string('house_number', 10)->nullable();
            $table->string('postal_code', 6);
            $table->string('city', 100);
            $table->foreignId('country_id')->nullable()->constrained('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('street');
            $table->dropColumn('house_number');
            $table->dropColumn('postal_code');
            $table->dropColumn('city');
            $table->dropColumn('country_id');
        });
    }
};
