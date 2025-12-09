<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Enums\PriorityLevel;
use App\Models\LocationType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Enums\NoticePeriodEnum;
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

class VehicleDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Voiture
        $user = User::factory()->create(['first_name' => 'Michel', 'last_name' => 'Dupont', 'email' => 'michel.dupont@sme-facility.com', 'job_position' => 'Commercial', 'phone_number' => '+3224586932']);
        $vehicleCategory = CategoryType::where('category', 'asset')->where('slug', 'asset-vehicle')->first();
        $assetVehicle = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Skoda Octavia 1-ZAB-930',
                    'description' => 'Skoda Octavia de Michel',
                    'under_warranty' => true,
                    'purchase_date' => Carbon::now()->subMonths(6),
                    'purchase_cost' => 32129.99,
                    'under_warranty' => true,
                    'end_warranty_date' => Carbon::now()->subMonths(6)->addYear(2),
                    'need_maintenance' => true,
                    'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
                    'last_maintenance_date' => null,
                    'next_maintenance_date' => Carbon::now()->subMonths(6)->addDays(MaintenanceFrequency::ANNUAL->days()),
                ]
            )
            ->forLocation($user)
            ->create(
                [
                    'category_type_id' => $vehicleCategory->id,
                    'brand' => 'Skoda',
                    'model' => 'Octavia',
                    'serial_number' => 'X25VCD6485MDD362AZ663',
                    'is_mobile' => true,
                    'surface' => null,
                    'has_meter_readings' => true,
                    "depreciable" => true,
                    "depreciation_start_date" => Carbon::now()->subMonths(6),
                    "depreciation_end_date" =>  Carbon::now()->subMonths(6)->addYear(5),
                    "depreciation_duration" =>  5,
                    'meter_unit' => 'km',
                    'meter_number' => '1-ZAB-930'
                ]
            );

        $assetVehicle->meterReadings()->create(
            [
                'meter' => 6025,
                'meter_date' => Carbon::now()->subMonths(5),
            ]
        );
        $assetVehicle->meterReadings()->create(
            [
                'meter' => 18025,
                'meter_date' => Carbon::now()->subMonths(4),
            ]
        );

        $assetVehicle->meterReadings()->create(
            [
                'meter' => 20025,
                'meter_date' => Carbon::now()->subMonths(3),
            ]
        );

        $assetVehicle->meterReadings()->create(
            [
                'meter' => 37025,
                'meter_date' => Carbon::now()->subMonths(2),
            ]
        );

        $assetVehicle->meterReadings()->create(
            [
                'meter' => 45025,
                'meter_date' => Carbon::now()->subMonths(1),
            ]
        );

        app(QRCodeService::class)->createAndAttachQR($assetVehicle);

        $assetVehicle->refresh();
        $assetVehicle->maintainable->providers()->sync([Provider::where('name', 'Garage de l\'automobile')->first()->id]);

        $provider = Provider::where('name', 'Garage de l\'automobile')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Leasing Skoda 2024-2026',
            'type' => ContractTypesEnum::ALLIN->value,
            'internal_reference' => 'LEASE_SKODA',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::now()->subYear()->addDays(15),
            'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
            'end_date' => ContractDurationEnum::ONE_YEAR->addTo(Carbon::now()->subYear()->addDays(15)),
            'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
            'notice_date' => NoticePeriodEnum::FOURTEEN_DAYS->subFrom(ContractDurationEnum::ONE_YEAR->addTo(Carbon::now()->subYear()->addDays(15))),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => 'Leasing pour toutes les Skoda',
        ]);

        $assetVehicle->contracts()->attach($contract);

        $interventionMaintenance = CategoryType::where('category', 'intervention')->where('slug', 'intervention-maintenance')->first();

        Intervention::factory()->forLocation($assetVehicle)->create([
            'description' => 'Entretien Skoda',
            'intervention_type_id' => $interventionMaintenance->id,
            'priority' => PriorityLevel::MEDIUM->value,
            'status' => InterventionStatus::PLANNED->value,
            'planned_at' => Carbon::yesterday(),
            'repair_delay' => null,
        ]);
    }
}
