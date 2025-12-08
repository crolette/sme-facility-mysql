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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('reference_code')->unique()->nullable();
            $table->unsignedBigInteger('level_id')->nullable();
            $table->unsignedBigInteger('location_type_id');
            $table->timestamps();

            $table->foreign('level_id')->references('id')->on('floors')->nullOnDelete();
            $table->index('code');
            $table->index('reference_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
