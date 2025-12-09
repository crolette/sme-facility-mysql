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
            $table->string('subscription_name', 50)->nullable();
            $table->string('subscription_plan', 50)->nullable();
            $table->unsignedTinyInteger('trial_days')->defaut(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('subscription_name');
            $table->dropColumn('subscription_plan');
            $table->dropColumn('trial_days');
        });
    }
};
