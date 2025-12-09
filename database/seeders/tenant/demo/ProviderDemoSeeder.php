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
        // Téléphonie
        $orange = Provider::factory()->create(
            [
                'name' => 'Orange sa',
                'email' => 'info@orange.be',
                'website' => 'https://www.orange.be',
                'vat_number' => 'BE8231796540',
                'phone_number' => '+32111222333',
                'street' => 'Avenue Louise',
                'house_number' => '134',
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_id' => Country::where('iso_code', 'BEL')->first()->id,
            ]
        );

        $phoneCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-telephony-internet-voip')->first();
        $orange->categories()->sync([$phoneCategory->id]);

        $paul = User::factory()->create(
            [
                'first_name' => 'Paul',
                'last_name' => 'Lamberts',
                'email' => 'pl@orange.be',
                'phone_number' => '+32319764285',
                'job_position' => 'Account Manager',
            ]
        );

        $paul->provider()->associate($orange)->save();


        // IT
        $itProvider = Provider::factory()->create(
            [
                'name' => 'Le comptoir de la ram',
                'email' => 'info@lecomptoirdelaram.be',
                'website' => 'https://www.lecomptoirdelaram.be',
                'vat_number' => 'BE6547908231',
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


        // Cleaning
        $cleaningProvider = Provider::factory()->create(
            [
                'name' => 'All Clean sa',
                'email' => 'info@allclean.be',
                'website' => 'https://www.allclean.be',
                'vat_number' => 'BE6908547231',
                'phone_number' => '+326352419870',
                'street' => 'Rue de la régence',
                'house_number' => '22',
                'postal_code' => '4040',
                'city' => 'Herstal',
                'country_id' => Country::where('iso_code', 'BEL')->first()->id,
            ]
        );

        $cleaningCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-cleaning')->first();
        $cleaningProvider->categories()->sync([$cleaningCategory->id]);

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

        $tobias = User::factory()->create(
            [
                'first_name' => 'Tobias',
                'last_name' => 'Schmitt',
                'email' => 'tobias.schmitt@lecomptoirdufroid.be',
                'phone_number' => '+32314289765',
                'job_position' => 'Réparateur',
            ]
        );

        $tobias->provider()->associate($hvacProvider)->save();

        $seb = User::factory()->create(
            [
                'first_name' => 'Sébastien',
                'last_name' => 'Dubois',
                'email' => 'seb.dubois@lecomptoirdufroid.be',
                'phone_number' => '+32328914765',
                'job_position' => 'Commercial',
            ]
        );

        $seb->provider()->associate($hvacProvider)->save();


        // Voiture
        $carProvider = Provider::factory()->create(
            [
                'name' => 'Garage de l\'automobile',
                'email' => 'info@garagedelautomobile.be',
                'website' => 'https://www.garagedelautomobile.be',
                'vat_number' => 'BE6548231790',
                'phone_number' => '+32472896315',
                'street' => 'Rue du volant',
                'house_number' => '12',
                'postal_code' => '4000',
                'city' => 'Liège',
                'country_id' => Country::where('iso_code', 'BEL')->first()->id,
            ]
        );

        $carCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-car')->first();
        $carProvider->categories()->sync([$carCategory->id]);

        $sales = User::factory()->create(
            [
                'first_name' => 'Max',
                'last_name' => 'Dubois',
                'email' => 'max.dubois@garagedelautomobile.be',
                'phone_number' => '+32328149765',
                'job_position' => 'Commercial',
            ]
        );

        $sales->provider()->associate($carProvider)->save();

        // Electricien
        $electrician = Provider::factory()->create(
            [
                'name' => 'Jacky Den SPRL',
                'email' => 'info@jackydensprl.be',
                'website' => 'https://www.jackydensprl.be',
                'vat_number' => 'BE6231754890',
                'phone_number' => '+32428976315',
                'street' => 'Rue du courant',
                'house_number' => '124',
                'postal_code' => '4000',
                'city' => 'Liège',
                'country_id' => Country::where('iso_code', 'BEL')->first()->id,
            ]
        );

        $electricityCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-electricity')->first();
        $securityCategory = CategoryType::where('category', 'provider')->where('slug', 'provider-security')->first();
        $electrician->categories()->sync([$electricityCategory->id, $securityCategory->id]);

        $jackyDen = User::factory()->create(
            [
                'first_name' => 'Jacky',
                'last_name' => 'Den',
                'email' => 'info@jackydensprl.be',
                'phone_number' => '+32428976315',
                'job_position' => 'Directeur',
            ]
        );

        $jackyDen->provider()->associate($electrician)->save();
    }
}
