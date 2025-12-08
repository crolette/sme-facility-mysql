<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Company;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Hash;
use App\Enums\ContractRenewalTypesEnum;
use App\Models\Tenants\MeterReading;
use App\Models\Tenants\ScheduledNotification;
use App\Services\UserNotificationPreferenceService;
use Database\Seeders\tenant\demo\VehicleDemoSeeder;
use Database\Seeders\tenant\demo\ContractDemoSeeder;
use Database\Seeders\tenant\demo\ProviderDemoSeeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Maintainable::truncate();
        DB::statement("TRUNCATE TABLE `provider_maintainable`");
        DB::statement("TRUNCATE TABLE `category_type_provider`");
        DB::statement("TRUNCATE TABLE `model_has_roles`");
        ScheduledNotification::truncate();
        Site::truncate();
        Building::truncate();
        Provider::truncate();
        Floor::truncate();
        MeterReading::truncate();
        Room::truncate();
        Asset::truncate();
        Contract::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $tenant = tenancy()->tenant;

        if (!User::where('email', 'super@sme-facility.com')->first()) {

            $user = User::factory()->withRole('Super Admin')->create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'super@sme-facility.com',
                'password' => Hash::make('@demo_SME!2026'),
                'can_login' => true
            ]);
        }

        if (!User::where('email', $tenant->email)->first()) {

            $admin = User::factory()->withRole('Admin')->create([
                'email' => $tenant->email,
                'first_name' => $tenant->first_name,
                'last_name' => $tenant->last_name,
                'can_login' => true,
                'password' => Hash::make('@demo_SME!2026'),
            ]);

            app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($admin);
        }

        User::factory()->withRole('Admin')->create(['first_name' => 'Alain', 'last_name' => 'Delahaut', 'email' => 'alain@facilitywebxp.be', 'job_position' => 'Administrateur', 'phone_number' => '+3224586932']);


        $this->call([
            ProviderDemoSeeder::class
        ]);

        $company = Company::first();
        $company->update([
            'last_ticket_number' => 0,
            'last_asset_number' => 0,
            'disk_size' => 0,
        ]);


        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();

        $site = Site::factory()->withMaintainableData([
            'name' => 'Site principal',
            'description' => 'Site de la démonstration',

        ])->create([
            'surface_floor' => 1523.0,
            'surface_walls' => 1523.0,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id
        ]);
        $building = Building::factory()->withMaintainableData(['name' => 'Bâtiment principal', 'description' => 'Bâtiment administratif'])->create();

        $floorGround = LocationType::where('level', 'floor')->where('slug', 'ground-floor')->first();
        $floorFloors = LocationType::where('level', 'floor')->where('slug', 'floors')->first();
        $floorGround = Floor::factory()->withMaintainableData([
            'name' => 'Rez-de-chaussée',
            'description' => 'Niveau 0',

        ])->create([
            'location_type_id' => $floorGround->id,
            'surface_floor' => 225.0,
            'surface_walls' => 35.0,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id
        ]);
        $floorOne = Floor::factory()->withMaintainableData([
            'name' => 'Etage 1',
            'description' => 'Etage direction',

        ])->create([
            'location_type_id' => $floorFloors->id,
            'surface_floor' => 210.0,
            'surface_walls' => 350.0,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id
        ]);

        $roomOfficeType = LocationType::where('level', 'room')->where('slug', 'office')->first();
        $roomTechnicalType = LocationType::where('level', 'room')->where('slug', 'technical-room')->first();
        $roomMeetingType = LocationType::where('level', 'room')->where('slug', 'meeting-room')->first();

        $roomOfficeSales = Room::factory()->withMaintainableData(['name' => 'Bureau vente', 'description' => 'Bureau des commerciaux'])->create([
            'level_id' => $floorGround->id,
            'location_type_id' => $roomOfficeType->id,
            'surface_floor' => 105.0,
            'surface_walls' => 210.0,
            'height' => 2.50,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id
        ]);

        $roomTechnical = Room::factory()->withMaintainableData([
            'name' => 'Local technique',
            'description' => 'Local chaufferie',

        ])->create([
            'level_id' => $floorGround->id,
            'location_type_id' => $roomTechnicalType->id,
            'surface_floor' => 25.0,
            'surface_walls' => 35.0,
            'height' => 2.50,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id,
        ]);

        $roomOfficeDirector = Room::factory()
            ->withMaintainableData(
                [
                    'name' => 'Bureau directeur',
                    'description' => 'Bureau du directeur',
                    'need_maintenance' => true,
                    'last_maintenance_date' => Carbon::now()->subWeek(),
                    'next_maintenance_date' => Carbon::tomorrow(),
                    'maintenance_frequency' => MaintenanceFrequency::WEEKLY->value,

                ]
            )
            ->create([
                'level_id' => $floorOne->id,
                'location_type_id' => $roomOfficeType->id,
                'surface_floor' => 50.0,
                'surface_walls' => 60.0,
                'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
                'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id,
                'height' => 2.50
            ]);


        $provider = Provider::where('name', 'All Clean sa')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Nettoyage, entretien des locaux',
            'type' => ContractTypesEnum::CLEANING->value,
            'internal_reference' => 'CLEAN_2025-12',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::createFromDate(2025, 01, 12),
            'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
            'end_date' => Carbon::createFromDate(2025, 01, 12)->addYear(),
            'notice_period' => NoticePeriodEnum::ONE_MONTH->value,
            'notice_date' => NoticePeriodEnum::ONE_MONTH->subFrom(Carbon::createFromDate(2025, 01, 12)->addYear()),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => fake()->text(50),
        ]);

        $roomOfficeDirector->contracts()->attach($contract);

        $roomMeeting = Room::factory()->withMaintainableData([
            'name' => 'Salle de réunion Einstein',
            'description' => 'Salle de réunion à côté du bureau du directeur',

        ])->create([
            'level_id' => $floorOne->id,
            'location_type_id' => $roomMeetingType->id,
            'surface_floor' => 75.0,
            'surface_walls' => 112.0,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id,
            'height' => 2.50
        ]);

        // Ordinateurs
        $itGuy = User::factory()->withRole('Maintenance Manager')->create(['first_name' => 'Michael', 'last_name' => 'Durand', 'email' => 'michel.durand@sme-facility.com', 'job_position' => 'IT', 'phone_number' => '+3243764376', 'can_login' => true]);
        app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($itGuy);


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
                "depreciation_start_date" => Carbon::now()->subYear(),
                "depreciation_end_date" => Carbon::now()->addYear(3),
                "depreciation_duration" =>  3,
                "surface" => null
            ]);

        $assetComputerSales->refresh();
        $assetComputerSales->maintainable->providers()->sync([Provider::where('name', 'Le comptoir de la ram')->first()->id]);

        $assetComputerDirector = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Laptop directeur ASUS',
                    'description' => 'PC portable du directeur',
                    'purchase_date' => Carbon::yesterday(),
                    'purchase_cost' => 1299.99,
                    'under_warranty' => true,
                    'end_warranty_date' => Carbon::yesterday()->addYear(2),
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
                "depreciation_start_date" => Carbon::yesterday(),
                "depreciation_end_date" => Carbon::yesterday()->addYear(2),
                "depreciation_duration" =>  2,
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
            'name' => 'Contrat de maintenance IT',
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

        // HVAC
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

        // Luminaire salle de réunion
        $lightingCategory = CategoryType::where('category', 'asset')->where('slug', 'asset-lighting')->first();
        $assetLighting = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Lampe plafond',
                    'description' => 'Lampe plafond - Socket GU10',
                ]
            )
            ->forLocation($roomMeeting)
            ->create([
                'category_type_id' => $lightingCategory->id,
                'brand' => 'Illudesign',
                'model' => 'Maxi-Lamp',
            ]);

        app(QRCodeService::class)->createAndAttachQR($assetComputerSales);
        app(QRCodeService::class)->createAndAttachQR($assetComputerDirector);

        app(QRCodeService::class)->createAndAttachQR($assetHvac);
        app(QRCodeService::class)->createAndAttachQR($assetLighting);

        $lightingCategory = CategoryType::where('category', 'asset')->where('slug', 'asset-lighting')->first();

        $assetLighting = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Lampe plafond',
                    'description' => 'Lampe plafond - Socket GU10',
                ]
            )
            ->forLocation($roomMeeting)
            ->create([
                'category_type_id' => $lightingCategory->id,
                'brand' => 'Illudesign',
                'model' => 'Maxi-Lamp',
            ]);

        $assetLighting->refresh();
        $assetLighting->maintainable->providers()->sync([Provider::where('name', 'Jacky Den SPRL')->first()->id]);

        $this->call([
            VehicleDemoSeeder::class,
            ContractDemoSeeder::class
        ]);
    }
}
