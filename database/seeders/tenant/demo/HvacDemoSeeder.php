<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Enums\PriorityLevel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Country;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Enums\InterventionStatus;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Enums\ContractRenewalTypesEnum;
use App\Models\Tenants\InterventionAction;
use Database\Factories\Tenants\InterventionActionFactory;

class HvacDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // HVAC
        $roomTechnical = Room::getByName('Local technique')->first();
        $hvacCategory = CategoryType::where('category', 'asset')->where('slug', 'asset-hvac')->first();
        $assetHvac = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Chaudière gaz',
                    'description' => 'Chaudière gaz Frisquet',
                    'need_maintenance' => true,
                    'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
                    'last_maintenance_date' => Carbon::now()->subYear(),
                    'next_maintenance_date' => Carbon::now()->tomorrow(),
                ]
            )
            ->forLocation($roomTechnical)
            ->create([
                'category_type_id' => $hvacCategory->id,
                'brand' => 'Frisquet',
                'model' => 'HYDROCONFORT',
                'serial_number' => '15869AD44PLD',
                'surface' => null,
                'has_meter_readings' => false,
                "depreciable" => false,
            ]);

        $assetHvac->refresh();
        $hvacProvider = Provider::where('name', 'Le comptoir du froid')->first();
        $assetHvac->maintainable->providers()->sync([$hvacProvider->id]);

        $contract = Contract::factory()->create([
            'provider_id' => $hvacProvider->id,
            'name' => 'Contrat de maintenance HVAC',
            'type' => ContractTypesEnum::MAINTENANCE->value,
            'internal_reference' => 'HVAC_MAINTENANCE',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::now()->subYear(),
            'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
            'end_date' => ContractDurationEnum::ONE_YEAR->addTo(Carbon::now()->subYear()),
            'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
            'notice_date' => NoticePeriodEnum::FOURTEEN_DAYS->subFrom(ContractDurationEnum::ONE_YEAR->addTo(Carbon::now()->subYear())),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => fake()->text(50),
        ]);

        $assetHvac->contracts()->attach($contract);
        app(QRCodeService::class)->createAndAttachQR($assetHvac);

        $interventionMaintenance = CategoryType::where('category', 'intervention')->where('slug', 'intervention-maintenance')->first();

        Intervention::factory()->forLocation($assetHvac)->create([
            'description' => 'Entretien annuel',
            'intervention_type_id' => $interventionMaintenance->id,
            'priority' => PriorityLevel::MEDIUM->value,
            'status' => InterventionStatus::PLANNED->value,
            'planned_at' => Carbon::tomorrow(),
            'repair_delay' => null,
        ]);

        $intervention = Intervention::factory()->forLocation($assetHvac)->create([
            'description' => 'Entretien annuel',
            'intervention_type_id' => $interventionMaintenance->id,
            'priority' => PriorityLevel::MEDIUM->value,
            'status' => InterventionStatus::COMPLETED->value,
            'planned_at' => Carbon::now()->subYear(),
            'repair_delay' => null,
        ]);

        $actionType = CategoryType::where('category', 'action')->where('slug', 'action-maintenance')->first();

        InterventionAction::factory()->forIntervention($intervention)->create([

            'action_type_id' => $actionType->id,
            'description' => 'Entretien fait, tout en ordre.',
            'intervention_date' => Carbon::now()->subYear()->addDay(),
            'started_at' => '13:58',
            'finished_at' => '15:20',
            'intervention_costs' => 250.25,
            'creator_email' => fake()->safeEmail()
        ]);


        $ticket = Ticket::factory()->forLocation($assetHvac)->create([
            'description' => 'La chaudière fuit',
            'reported_by' => User::role('Maintenance Manager')->first(),
            'created_at' => Carbon::now()->subMonths(6),
            'status' => TicketStatus::CLOSED->value,
            'closed_at' => Carbon::now()->subMonths(6)->addDays(4),
            'closed_by' => User::role('Maintenance Manager')->first()
        ]);

        $interventionRepair = CategoryType::where('category', 'intervention')->where('slug', 'intervention-repair')->first();
        $intervention = Intervention::factory()->forTicket($ticket)->create([
            'description' => 'La chaudière fuit',
            'intervention_type_id' => $interventionRepair->id,
            'priority' => PriorityLevel::URGENT->value,
            'status' => InterventionStatus::COMPLETED->value,
            'planned_at' => Carbon::now()->subMonths(6),
            'repair_delay' => null,
        ]);

        $actionType = CategoryType::where('category', 'action')->where('slug', 'action-repair')->first();
        InterventionAction::factory()->forIntervention($intervention)->create([
            'action_type_id' => $actionType->id,
            'description' => 'Une pièce a dû être remplacée. Elle était sous garantie.',
            'intervention_date' => Carbon::now()->subMonths(6)->addDays(4),
            'started_at' => '09:30',
            'finished_at' => '12:00',
            'intervention_costs' => 0.0,
            'creator_email' => fake()->safeEmail()
        ]);
    }
}
