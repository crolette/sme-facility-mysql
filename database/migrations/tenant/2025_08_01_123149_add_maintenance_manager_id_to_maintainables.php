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
            $table->foreignId('maintenance_manager_id')->nullable()->constrained('users', 'id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainables', function (Blueprint $table) {
            $table->dropColumn('maintenance_manager_id');
        });
    }
};
