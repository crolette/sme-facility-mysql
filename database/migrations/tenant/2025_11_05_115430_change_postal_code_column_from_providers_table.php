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
            $table->string('postal_code', 8)->change();
            $table->string('vat_number', 14)->nullable()->change();
            $table->string('phone_number', 16)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('postal_code', 6)->change();
            $table->string('vat_number')->nullable()->change();
            $table->string('phone_number')->change();
        });
    }
};
