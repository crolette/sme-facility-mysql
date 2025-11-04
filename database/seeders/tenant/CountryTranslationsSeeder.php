<?php

namespace Database\Seeders\tenant;

use App\Models\Tenants\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenants\CountryTranslation;

class CountryTranslationsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $translations = include('countries_translations.php');

        foreach ($translations as $isoCode => $locales) {
            dump($isoCode);
            $country = Country::where('iso_code', $isoCode)->first();

            if ($country) {

                foreach ($locales as $locale => $label) {
                    CountryTranslation::create([
                        'country_id' => $country->id,
                        'locale' => $locale,
                        'label' => $label,
                    ]);
                }
            }
        }
    }
}
