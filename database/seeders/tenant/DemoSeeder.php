<?php

namespace Database\Seeders\tenant;

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Hash;
use App\Services\UserNotificationPreferenceService;
use Database\Seeders\tenant\demo\VehicleDemoSeeder;
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
        Site::truncate();
        Building::truncate();
        Provider::truncate();
        Floor::truncate();
        Room::truncate();
        Asset::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call([
            ProviderDemoSeeder::class
        ]);

        $tenant = tenancy()->tenant;

        if (!User::where('email', 'super@sme-facility.com')->first()) {

            $user = User::create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'super@sme-facility.com',
                'password' => Hash::make('SME_2025!fwebxp'),
                'can_login' => true
            ]);
            $user->assignRole('Super Admin');
        } else {
            $user = User::where('email', 'super@sme-facility.com')->first();
            $user->assignRole('Super Admin');
        }

        if (!User::where('email', $tenant->email)->first()) {

            $admin = User::create([
                'email' => $tenant->email,
                'first_name' => $tenant->first_name,
                'last_name' => $tenant->last_name,
                'can_login' => true,
                'password' => Hash::make('@demo_SME!2026'),
            ]);

            $admin->assignRole('Admin');

            app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($admin);
        }

        $company = Company::first();
        $company->update([
            'last_ticket_number' => 0,
            'last_asset_number' => 0,
            'disk_size' => 0,
        ]);


        $site = Site::factory()->withMaintainableData(['name' => 'Site principal', 'description' => 'Site de la démonstration'])->create();
        $building = Building::factory()->withMaintainableData(['name' => 'Bâtiment principal', 'description' => 'Bâtiment administratif'])->create();

        $floorGround = LocationType::where('level', 'floor')->where('slug', 'ground-floor')->first();
        $floorFloors = LocationType::where('level', 'floor')->where('slug', 'floors')->first();
        $floorGround = Floor::factory()->withMaintainableData(['name' => 'Rez-de-chaussée', 'description' => 'Niveau 0'])->create(['location_type_id' => $floorGround->id]);
        $floorOne = Floor::factory()->withMaintainableData(['name' => 'Etage 1', 'description' => 'Etage direction'])->create(['location_type_id' => $floorFloors->id]);

        $roomOfficeType = LocationType::where('level', 'room')->where('slug', 'office')->first();
        $roomTechnicalType = LocationType::where('level', 'room')->where('slug', 'technical-room')->first();
        $roomMeetingType = LocationType::where('level', 'room')->where('slug', 'meeting-room')->first();

        $roomOfficeSales = Room::factory()->withMaintainableData(['name' => 'Bureau vente', 'description' => 'Bureau des commerciaux'])->create(['level_id' => $floorGround->id, 'location_type_id' => $roomOfficeType->id]);

        $roomTechnical = Room::factory()->withMaintainableData(['name' => 'Local technique', 'description' => 'Local chaufferie'])->create(['level_id' => $floorGround->id, 'location_type_id' => $roomTechnicalType->id]);

        $roomOfficeDirector = Room::factory()->withMaintainableData(['name' => 'Bureau directeur', 'description' => 'Bureau du directeur'])->create(['level_id' => $floorOne->id, 'location_type_id' => $roomOfficeType->id]);

        $roomMeeting = Room::factory()->withMaintainableData(['name' => 'Salle de réunion Einstein', 'description' => 'Salle de réunion à côté du bureau du directeur'])->create(['level_id' => $floorOne->id, 'location_type_id' => $roomMeetingType->id]);

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
        $assetHvac->maintainable->providers()->sync([Provider::where('name', 'Le comptoir du froid')->first()->id]);


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

        $this->call([
            VehicleDemoSeeder::class,
        ]);
    }
}
