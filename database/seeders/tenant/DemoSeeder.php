<?php

namespace Database\Seeders\tenant;

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
use Illuminate\Support\Facades\DB;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Hash;
use App\Services\UserNotificationPreferenceService;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     PermissionsSeeder::class,
        //     ContractsPermissionsSeeder::class,
        //     ProvidersPermissionsSeeder::class,
        //     CountriesSeeder::class,
        //     CountryTranslationsSeeder::class,
        // ]);


        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Maintainable::truncate();
        Site::truncate();
        Building::truncate();
        Floor::truncate();
        Room::truncate();
        Asset::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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


        $site = Site::factory()->withMaintainableData(['name' => 'Nouveau site', 'description' => 'Nouvelle description'])->create();
        $building = Building::factory()->create();

        $floorGround = LocationType::where('level', 'floor')->where('slug', 'ground-floor')->first();
        $floorFloors = LocationType::where('level', 'floor')->where('slug', 'floors')->first();
        $floorGround = Floor::factory()->withMaintainableData()->create(['location_type_id' => $floorGround->id]);
        $floorOne = Floor::factory()->withMaintainableData()->create(['location_type_id' => $floorFloors->id]);

        $roomOffice = LocationType::where('level', 'room')->where('slug', 'office')->first();
        $roomTechnical = LocationType::where('level', 'room')->where('slug', 'technical-room')->first();
        $roomMeeting = LocationType::where('level', 'room')->where('slug', 'meeting-room')->first();
        $roomOne = Room::factory()->withMaintainableData()->create(['level_id' => $floorGround->id, 'location_type_id' => $roomOffice->id]);
        $roomTwo = Room::factory()->withMaintainableData()->create(['level_id' => $floorGround->id, 'location_type_id' => $roomTechnical->id]);
        $roomThree = Room::factory()->withMaintainableData()->create(['level_id' => $floorOne->id, 'location_type_id' => $roomOffice->id]);
        $roomFour = Room::factory()->withMaintainableData()->create(['level_id' => $floorOne->id, 'location_type_id' => $roomMeeting->id]);

        $assetCategories = CategoryType::where('category', 'asset')->get();

        $nbAssetCategories = count($assetCategories);

        $assetOne = Asset::factory()->withMaintainableData()->forLocation($roomOne)->create(['category_type_id' => $assetCategories[rand(0, $nbAssetCategories - 1)]->id]);
        $assetTwo = Asset::factory()->withMaintainableData()->forLocation($roomTwo)->create(['category_type_id' => $assetCategories[rand(0, $nbAssetCategories - 1)]->id]);
        $assetThree = Asset::factory()->withMaintainableData()->forLocation($roomThree)->create(['category_type_id' => $assetCategories[rand(0, $nbAssetCategories - 1)]->id]);
        $assetFour = Asset::factory()->withMaintainableData()->forLocation($roomFour)->create(['category_type_id' => $assetCategories[rand(0, $nbAssetCategories - 1)]->id]);

        app(QRCodeService::class)->createAndAttachQR($assetOne);
        app(QRCodeService::class)->createAndAttachQR($assetTwo);
        app(QRCodeService::class)->createAndAttachQR($assetThree);
        app(QRCodeService::class)->createAndAttachQR($assetFour);
    }
}
