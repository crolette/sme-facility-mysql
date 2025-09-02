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
        Schema::create('scheduled_notifications', function (Blueprint $table) {
            $table->id();
            // relation polymorphique : type: Asset, Ticket, Site, Contract, ...
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');

            // types : maintenance, warranty, depreciation, contract, intervention
            $table->string('notification_type');

            $table->date('scheduled_at');
            $table->timestamp('sent_at')->nullable();

            $table->string('recipient_email', 255);
            $table->string('recipient_name')->nullable();

            // status : pending, sent, failed, cancelled
            $table->string('status')->default('pending');

            $table->string('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->nullable();


            //             {
            //     "contract_name": "Contrat nettoyage bureaux",
            //     "contract_reference": "CNT-2024-001",
            //     "supplier_name": "Entreprise XYZ",
            //     "supplier_contact": "contact@xyz.com",
            //     "expiry_date": "2024-12-31",
            //     "location": "Bâtiment A - Étage 3",
            //     "asset_serial": "ABC123",
            //     "dashboard_url": "https://app.com/contracts/15",
            //     "responsible_person": "John Doe",
            //     "responsible_email": "john@company.com"
            // }

            // Exemple de JSON
            // [
            //     'asset/location name' => 'Photocopieur Xerox'/'Rez-de-chaussée',
            //     'due_date' => '2024-12-31',
            //     'dashboard_url' => 'https://app.com/contracts/15'
            //      
            //     'contract_name' => 'Contrat nettoyage',
            //     'supplier_name' => 'Entreprise XYZ',
            //     'contract_reference' => 'CNT-2024-001',
            // ]
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_notifications');
    }
};
