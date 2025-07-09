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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('reference_code')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('location_type_id');
            $table->timestamps();

            $table->index('code');
            $table->index('reference_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
