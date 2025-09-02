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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // asset types
            // asset, site, building, floor, room, contract, ticket, maintenance
            $table->string('asset_type');

            // types and based columns: 
            // contract : notice_period
            // maintenance : next_maintenance_date
            // interventions: planned_at
            // depreciation : depreciation_end_date
            // warranty : end_of_warranty
            // ticket : if the user wants to be notified if a new ticket is created
            $table->string('notification_type');

            // when the notification should be sent
            $table->unsignedTinyInteger('notification_delay_days')->default(1);
            $table->boolean('enabled')->default(true);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
