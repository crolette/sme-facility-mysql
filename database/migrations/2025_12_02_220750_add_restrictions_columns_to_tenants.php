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
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_sites')->default(0);
            $table->unsignedTinyInteger('max_users')->default(0);
            $table->unsignedTinyInteger('max_storage_gb')->default(0);
            $table->boolean('has_statistics')->default(false);
            $table->unsignedTinyInteger('current_sites_count')->default(0);
            $table->unsignedTinyInteger('current_users_count')->default(0);
            $table->unsignedBigInteger('current_storage_bytes')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('max_sites');
            $table->dropColumn('max_users');
            $table->dropColumn('max_storage');
            $table->dropColumn('has_statistics');
            $table->dropColumn('current_sites_count');
            $table->dropColumn('current_users_count');
            $table->dropColumn('current_storage_bytes');
        });
    }
};
