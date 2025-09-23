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
        Schema::table('intervention_actions', function (Blueprint $table) {
            $table->decimal('intervention_costs', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervention_actions', function (Blueprint $table) {
            $table->double('intervention_costs')->nullable()->change();
        });
    }
};
