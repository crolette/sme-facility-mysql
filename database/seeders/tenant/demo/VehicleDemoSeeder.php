<?php

namespace Database\Seeders\tenant\demo;

use App\Enums\MaintenanceFrequency;
use Carbon\Carbon;
use App\Models\LocationType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Models\Central\CategoryType;

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
    }
}
