<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Country;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Models\Tenants\Provider;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;

class ProviderDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // IT

        $itProvider = Provider::factory()->create(
            [
                'name' => 'Le comptoir de la ram',
                'email' => 'info@lecomptoirdelaram.be',
                'website' => 'https://www.lecomptoirdelaram.be',
                'vat_number' => 'BE6548231790',
                'phone_number' => '+32111222333',
                'street' => 'Rue de la station',
                'house_number' => '18',
                'postal_code' => '4000',
                'city' => 'Liège',
                'country_id' => Country::where('iso_code', 'BEL')->first()->id,
            ]
        );

        $itCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-it-equipment')->first();
        $itProvider->categories()->sync([$itCategory->id]);


        // HVAC
        $hvacProvider = Provider::factory()->create(
            [
                'name' => 'Le comptoir du froid',
                'email' => 'info@lecomptoirdufroid.be',
                'website' => 'https://www.lecomptoirdufroid.be',
                'vat_number' => 'BE8236541790',
                'phone_number' => '+32963147285',
                'street' => 'Rue de la source',
                'house_number' => '36',
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_id' => Country::where('iso_code', 'BEL')->first()->id,
            ]
        );

        $hvacCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-hvac')->first();
        $hvacProvider->categories()->sync([$hvacCategory->id]);
    }
}
