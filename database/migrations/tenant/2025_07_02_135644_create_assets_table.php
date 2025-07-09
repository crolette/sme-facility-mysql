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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('reference_code')->unique();

            $table->string('location_type');
            $table->unsignedBigInteger('location_id');

            $table->timestamps();
            $table->index('code');
            $table->index('reference_code');
            $table->index(['location_type', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
