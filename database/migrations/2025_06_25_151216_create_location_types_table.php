<?php

use App\Enums\LevelTypes;
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


        Schema::create('location_types', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 4)->unique();
            $table->string('slug')->unique();
            $table->enum('level', array_column(LevelTypes::cases(), 'value'))->default(LevelTypes::SITE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_types');
    }
};
