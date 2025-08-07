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
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('floor_material_id')->nullable();
            $table->text('floor_material_other', 50)->nullable();
            $table->unsignedBigInteger('wall_material_id')->nullable();
            $table->text('wall_material_other', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('floor_material_id');
            $table->dropColumn('floor_material_other');
            $table->dropColumn('wall_material_id');
            $table->dropColumn('wall_material_other');
        });
    }
};
