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
        Schema::table('maintainables', function (Blueprint $table) {
            $table->boolean('need_maintenance')->default(false);
            $table->date('next_maintenance_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->string('maintenance_frequency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainables', function (Blueprint $table) {
            $table->dropColumn('need_maintenance');
            $table->dropColumn('next_maintenance_date');
            $table->dropColumn('last_maintenance_date');
            $table->dropColumn('maintenance_frequency');
        });
    }
};
