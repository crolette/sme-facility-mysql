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
        Schema::table('company', function (Blueprint $table) {
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn('logo');
            $table->dropColumn('address');
            $table->dropColumn('vat_number');
            $table->dropColumn('name');
        });
    }
};
