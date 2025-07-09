<?php

use App\Enums\CategoryTypes;
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
        Schema::create('category_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('category', array_column(CategoryTypes::cases(), 'value'))->default(CategoryTypes::DOCUMENT->value);
            $table->timestamps();

            $table->unique(['slug', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_types');
    }
};
