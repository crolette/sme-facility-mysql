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
        Schema::table('domains', function (Blueprint $table) {
            $table->dropUnique('domains_name_unique');
            $table->renameColumn('name', 'domain');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->unique('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropUnique('domains_domain_unique');
            $table->renameColumn('domain', 'name');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
