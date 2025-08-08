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
        Schema::table('buildings', function (Blueprint $table) {
            $table->decimal('surface_outdoor', 9, 2)->nullable();
            $table->unsignedBigInteger('outdoor_material_id')->nullable();
            $table->text('outdoor_material_other', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn('surface_outdoor');
            $table->dropColumn('outdoor_material_id');
            $table->dropColumn('outdoor_material_other');
        });
    }
};
