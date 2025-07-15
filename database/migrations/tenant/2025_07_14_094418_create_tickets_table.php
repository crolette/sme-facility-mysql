<?php

use App\Enums\TicketStatus;
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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->enum('status', array_column(TicketStatus::cases(), 'value'))->default(TicketStatus::OPEN->value);

            $table->text('description');
            $table->string('ticketable_type');
            $table->unsignedBigInteger('ticketable_id');

            // id of the reporter if logged in
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();

            // email of the reporter if not logged in
            $table->string('reporter_email')->nullable();

            // if the reporter wants to be notified of statuses changes
            $table->boolean('being_notified')->default(false);

            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['ticketable_type', 'ticketable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
