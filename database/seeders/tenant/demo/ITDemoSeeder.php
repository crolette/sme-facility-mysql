<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Models\LocationType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;

class ITDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Contract


        // Téléphonie Orange
        $provider = Provider::where('name', 'Orange sa')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Abonnements Orange Basic',
            'type' => ContractTypesEnum::OTHER->value,
            'internal_reference' => 'PHONE',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::yesterday()->subYears(2),
            'contract_duration' => ContractDurationEnum::TWO_YEARS->value,
            'end_date' => ContractDurationEnum::TWO_YEARS->addTo(Carbon::yesterday()->subYears(2)),
            'notice_period' => null,
            'notice_date' => null,
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::EXPIRED,
            'notes' => fake()->text(50),
        ]);

        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Abonnements Orange Premium',
            'type' => ContractTypesEnum::OTHER->value,
            'internal_reference' => 'PHONE',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::now()->subMonths(6),
            'contract_duration' => ContractDurationEnum::TWO_YEARS->value,
            'end_date' => ContractDurationEnum::TWO_YEARS->addTo(Carbon::now()->subMonths(6)),
            'notice_period' => NoticePeriodEnum::ONE_MONTH->value,
            'notice_date' => NoticePeriodEnum::ONE_MONTH->subFrom(ContractDurationEnum::TWO_YEARS->addTo(Carbon::now()->subMonths(6))),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => fake()->text(50),
        ]);


        // Ordinateurs
        $itGuy = User::factory()->withRole('Maintenance Manager')->create(['first_name' => 'Michael', 'last_name' => 'Durand', 'email' => 'michel.durand@sme-facility.com', 'job_position' => 'IT', 'phone_number' => '+3243764376', 'can_login' => true]);


        $roomOfficeSales = Room::getByName('Bureau vente')->first();

        $computerCategory = CategoryType::where('category', 'asset')->where('slug', 'asset-computer-hardware')->first();
        $assetComputerSales = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'PC de Bureau HP commercial interne',
                    'description' => 'PC de bureau pour les commerciaux internes',
                    'purchase_date' => Carbon::now()->subYear(),
                    'purchase_cost' => 899.99,
                    'under_warranty' => true,
                    'end_warranty_date' => Carbon::now()->addYear(2),
                    'maintenance_manager_id' => $itGuy->id,
                ]
            )
            ->forLocation($roomOfficeSales)
            ->create([
                'category_type_id' => $computerCategory->id,
                'brand' => 'HP',
                'model' => 'Pavilion H25B',
                'serial_number' => 'X25-ABC-96',
                "depreciable" => true,
                "depreciation_start_date" => Carbon::tomorrow()->subYears(2)->addDays(8),
                "depreciation_end_date" => Carbon::now()->tomorrow()->addDays(8),
                "depreciation_duration" =>  2,
                "surface" => null
            ]);


        $assetComputerSales->refresh();
        $assetComputerSales->maintainable->providers()->sync([Provider::where('name', 'Le comptoir de la ram')->first()->id]);

        $assetComputerSales->update(
            [
                "depreciation_start_date" => Carbon::tomorrow()->subYears(2)->addDays(7),
                "depreciation_end_date" => Carbon::now()->tomorrow()->addDays(7),
                "depreciation_duration" =>  2,
            ]
        );

        $roomOfficeDirector = Room::getByName('Bureau directeur')->first();

        $assetComputerDirector = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Laptop directeur ASUS',
                    'description' => 'PC portable du directeur',
                    'purchase_date' => Carbon::tomorrow()->subYears(2),
                    'purchase_cost' => 1299.99,
                    'under_warranty' => true,
                    'end_warranty_date' => Carbon::tomorrow(),
                    'maintenance_manager_id' => $itGuy->id,
                ]
            )
            ->forLocation($roomOfficeDirector)
            ->create([
                'category_type_id' => $computerCategory->id,
                'brand' => 'Asus',
                'model' => 'Vivobook E25F',
                'serial_number' => 'AZ5-CD-257BC',
                "depreciable" => true,
                "depreciation_start_date" => Carbon::tomorrow()->subYears(2),
                "depreciation_end_date" => Carbon::tomorrow()->subYears(2)->addYear(3),
                "depreciation_duration" =>  3,
                "surface" => null
            ]);

        $assetComputerDirector->refresh();
        $assetComputerDirector->maintainable->providers()->sync([Provider::where('name', 'Le comptoir de la ram')->first()->id]);



        $assetSoftDeleted = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'PC de Bureau HP de la secrétaire',
                    'description' => 'PC de bureau à l\'accueil',
                    'purchase_date' => Carbon::yesterday()->subYears(4),
                    'purchase_cost' => 599.99,
                    'under_warranty' => false,
                    'end_warranty_date' => Carbon::yesterday()->subYears(2),
                    'maintenance_manager_id' => $itGuy->id,
                ]
            )
            ->forLocation($roomOfficeSales)
            ->create([
                'category_type_id' => $computerCategory->id,
                'brand' => 'HP',
                'model' => 'Pavilion H25B',
                'serial_number' => 'X25-ABC-96',
                "depreciable" => false,
                "depreciation_start_date" => null,
                "depreciation_end_date" => null,
                "depreciation_duration" =>  null,
                "surface" => null,
                "deleted_at" => Carbon::yesterday()->subYears(2),
            ]);

        $assetSoftDeleted->refresh();
        $assetSoftDeleted->maintainable->providers()->sync([Provider::where('name', 'Le comptoir de la ram')->first()->id]);

        $provider = Provider::where('name', 'Le comptoir de la ram')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Maintenance  IT',
            'type' => ContractTypesEnum::ONDEMAND->value,
            'internal_reference' => 'PC Repair',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::yesterday()->subYears(2),
            'contract_duration' => ContractDurationEnum::SIX_MONTHS->value,
            'end_date' => ContractDurationEnum::SIX_MONTHS->addTo(Carbon::yesterday()->subYears(2)),
            'notice_period' => null,
            'notice_date' => null,
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::CANCELLED,
            'notes' => fake()->text(50),
        ]);

        $assetComputerSales->contracts()->attach($contract);
        $assetComputerDirector->contracts()->attach($contract);



        $assetPrinter = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Imprimante Xerox',
                    'description' => 'Imprimante pour impression brochure',
                    'purchase_date' =>  Carbon::now()->subYear(),
                    'purchase_cost' => 4899.99,
                    'under_warranty' => true,
                    'end_warranty_date' => Carbon::now()->addYear(),
                    'maintenance_manager_id' => $itGuy->id,
                ]
            )
            ->forLocation($roomOfficeSales)
            ->create([
                'category_type_id' => $computerCategory->id,
                'brand' => 'Xerox',
                'model' => 'X25-PRINT-HD',
                'serial_number' => 'X25-HD-XER',
                "depreciable" => true,
                "depreciation_start_date" => Carbon::now()->subYear(),
                "depreciation_end_date" => Carbon::now()->addYear(3),
                "depreciation_duration" =>  3,
                "surface" => null
            ]);

        $assetPrinter->refresh();
        $assetPrinter->maintainable->providers()->sync([Provider::where('name', 'Le comptoir de la ram')->first()->id]);

        $ticket = Ticket::factory()->forLocation($assetPrinter)->create([
            'description' => 'N\'imprime plus que en noir et blanc',
            'reported_by' => User::role('Maintenance Manager')->first(),
            'created_at' => Carbon::now(),
            'status' => TicketStatus::ONGOING->value,
        ]);

        app(QRCodeService::class)->createAndAttachQR($assetComputerSales);
        app(QRCodeService::class)->createAndAttachQR($assetComputerDirector);
        app(QRCodeService::class)->createAndAttachQR($assetPrinter);
    }
}
