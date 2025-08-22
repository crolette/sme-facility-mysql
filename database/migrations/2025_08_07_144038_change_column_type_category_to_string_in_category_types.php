<?php

use App\Enums\CategoryTypes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('category_types', function (Blueprint $table) {
            $table->string('category')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_types', function (Blueprint $table) {
            $table->enum('category', array_column(CategoryTypes::cases(), 'value'))->default(CategoryTypes::DOCUMENT->value);
        });
    }
};
