<?php

use App\Enums\TicketStatus;
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
        Schema::table('tickets', function (Blueprint $table) {
            $table->tinyText('description')->change();
            $table->tinyText('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->enum('status', array_column(TicketStatus::cases(), 'value'))->default(TicketStatus::OPEN->value)->change();
        });
    }
};
