<?php

use App\Enums\PriorityLevel;
use App\Enums\InterventionStatus;
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
        Schema::table('interventions', function (Blueprint $table) {
            $table->decimal('total_costs', 12, 2)->nullable()->change();
            $table->text('description', 1000)->nullable()->change();
            $table->tinyText('priority')->change();
            $table->tinyText('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->double('total_costs')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->enum('priority', array_column(PriorityLevel::cases(), 'value'))->default(PriorityLevel::MEDIUM->value)->change();
            $table->enum('status', array_column(InterventionStatus::cases(), 'value'))->default(InterventionStatus::DRAFT->value)->change();
        });
    }
};
