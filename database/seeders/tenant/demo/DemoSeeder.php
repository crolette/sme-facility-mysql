<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Enums\PriorityLevel;
use App\Models\LocationType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Company;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Enums\InterventionStatus;
use Illuminate\Support\Facades\DB;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\Maintainable;
use App\Models\Tenants\MeterReading;
use Illuminate\Support\Facades\Hash;
use App\Enums\ContractRenewalTypesEnum;
use App\Models\Tenants\InterventionAction;
use App\Models\Tenants\ScheduledNotification;
use Database\Seeders\tenant\demo\ITDemoSeeder;
use Database\Seeders\tenant\demo\HvacDemoSeeder;
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
        $tenant = tenancy()->tenant;
        if ($tenant->id !== 'demo')
            return;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Maintainable::truncate();
        DB::statement("TRUNCATE TABLE `provider_maintainable`");
        DB::statement("TRUNCATE TABLE `category_type_provider`");
        DB::statement("TRUNCATE TABLE `model_has_roles`");
        DB::statement("TRUNCATE TABLE `contractables`");
        ScheduledNotification::truncate();
        Site::truncate();
        Building::truncate();
        Provider::truncate();
        Floor::truncate();
        Ticket::truncate();
        MeterReading::truncate();
        Room::truncate();
        Asset::truncate();
        Contract::truncate();
        Intervention::truncate();
        InterventionAction::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');




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

        Site::factory()->withMaintainableData([
            'name' => 'Site principal',
            'description' => 'Site de la démonstration',

        ])->create([
            'surface_floor' => 1523.0,
            'surface_walls' => 1523.0,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id
        ]);

        Building::factory()->withMaintainableData(['name' => 'Bâtiment principal', 'description' => 'Bâtiment administratif'])->create();

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

        Room::factory()->withMaintainableData(['name' => 'Bureau vente', 'description' => 'Bureau des commerciaux'])->create([
            'level_id' => $floorGround->id,
            'location_type_id' => $roomOfficeType->id,
            'surface_floor' => 105.0,
            'surface_walls' => 210.0,
            'height' => 2.50,
            'floor_material_id' => $floorMaterials[rand(0, count($floorMaterials) - 1)]->id,
            'wall_material_id' => $wallMaterials[rand(0, count($wallMaterials) - 1)]->id
        ]);

        Room::factory()->withMaintainableData([
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


        app(QRCodeService::class)->createAndAttachQR($assetLighting);

        $ticket = Ticket::factory()->forLocation($assetLighting)->create(['description' => 'Ampoule ne fonctionne plus', 'reported_by' => $admin->id, 'created_at' => Carbon::yesterday()]);

        $interventionRepair = CategoryType::where('category', 'intervention')->where('slug', 'intervention-repair')->first();
        $intervention = Intervention::factory()->forTicket($ticket)->create([
            'description' => '2 ampoules à remplacer',
            'intervention_type_id' => $interventionRepair->id,
            'priority' => PriorityLevel::URGENT->value,
            'status' => InterventionStatus::COMPLETED->value,
            'planned_at' => Carbon::now(),
            'repair_delay' => null,
        ]);

        $actionType = CategoryType::where('category', 'action')->where('slug', 'action-repair')->first();
        InterventionAction::factory()->forIntervention($intervention)->create([
            'action_type_id' => $actionType->id,
            'description' => 'Ampoules changées',
            'intervention_date' => Carbon::now(),
            'started_at' => '09:30',
            'finished_at' => '09:40',
            'intervention_costs' => 0.0,
            'creator_email' => fake()->safeEmail()
        ]);

        $assetLightingHalo = Asset::factory()
            ->withMaintainableData(
                [
                    'name' => 'Lampe plafond',
                    'description' => 'Lampe plafond - Halogène',
                ]
            )
            ->forLocation($floorGround)
            ->create([
                'category_type_id' => $lightingCategory->id,
                'brand' => 'Illudesign',
                'model' => 'Lamp-Halo',
            ]);

        $assetLightingHalo->refresh();
        $assetLightingHalo->maintainable->providers()->sync([Provider::where('name', 'Jacky Den SPRL')->first()->id]);

        $ticket = Ticket::factory()->forLocation($assetLightingHalo)->create(['description' => 'Ampoule clignote', 'reported_by' => $admin->id, 'created_at' => Carbon::yesterday()]);

        app(QRCodeService::class)->createAndAttachQR($assetLightingHalo);

        $this->call([
            VehicleDemoSeeder::class,
            ITDemoSeeder::class,
            HvacDemoSeeder::class,
            ContractDemoSeeder::class
        ]);
    }
}
