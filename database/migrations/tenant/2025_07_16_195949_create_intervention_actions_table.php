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
        Schema::create('intervention_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('action_type_id');

            $table->text('description')->nullable();
            $table->date('intervention_date')->nullable();
            $table->time('started_at')->nullable();
            $table->time('finished_at')->nullable();

            $table->float('intervention_costs')->nullable();

            // id of the creator if logged in
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // email of the creator if not logged in
            $table->string('creator_email')->nullable();


            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention_actions');
    }
};
