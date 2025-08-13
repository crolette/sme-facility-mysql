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
        Schema::table('assets', function (Blueprint $table) {
            $table->date("depreciation_start_date")->nullable();
            $table->date("depreciation_end_date")->nullable();
            $table->unsignedTinyInteger('depreciation_duration')->nullable();
            $table->decimal('residual_value', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn("depreciation_start_date ");
            $table->dropColumn("depreciation_end_date");
            $table->dropColumn('depreciation_duration');
            $table->dropColumn('residual_value', 12, 2);
        });
    }
};
