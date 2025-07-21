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
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();

            // Relation vers la table centrale avec les types d'interventions
            $table->unsignedBigInteger('intervention_type_id');
            $table->enum('priority', array_column(PriorityLevel::cases(), 'value'))->default(PriorityLevel::MEDIUM->value);
            $table->enum('status', array_column(InterventionStatus::cases(), 'value'))->default(InterventionStatus::DRAFT->value);

            $table->date('planned_at')->nullable();
            $table->text('description')->nullable();

            // Délai de réparation : Date à laquelle cela devrait être réparé
            $table->date('repair_delay')->nullable();
            $table->float('total_costs')->nullable();

            // Relation directe vers le maintainable de asset, site, building, floor, room pour garder la logique liée à maintainable qui est le point central pour la maintenance
            $table->foreignId('maintainable_id')->constrained()->cascadeOnDelete();

            // Relation polymorphe vers assets, site, building, floor, room pour pouvoir filtrer rapidement en fonction de l'emplacement
            $table->nullableMorphs('interventionable');

            // Relation nullable vers un ticket si l'intervention est liée à un ticket
            $table->foreignId('ticket_id')->nullable()->constrained()->cascadeOnDelete();

            $table->timestamps();
            $table->index(['maintainable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
