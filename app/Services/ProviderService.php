<?php

namespace App\Services;

use Exception;
use App\Models\Tenants\Company;
use App\Models\Tenants\Country;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProviderService
{
    public function create(array $data): Provider
    {

        $provider = new Provider([...$data]);

        $provider = $this->associateCountry($provider, $data['country_code']);
        $provider->save();
        $provider =  $this->attachCategories($provider, $data['categories']);
        return $provider;
    }



    public function update(Provider $provider, array $data): Provider
    {
        $provider->update([...$data]);

        $provider = $this->associateCountry($provider, $data['country_code']);
        $provider =  $this->attachCategories($provider, $data['categories']);

        $provider->save();

        return $provider;
    }

    private function associateCountry($provider, $countryCode)
    {
        $country = Country::where('iso_code', $countryCode)->first();
        $provider->country()->associate($country);
        return $provider;
    }

    private function attachCategories($provider, $categories)
    {
        $categoriesCollection = collect($categories);

        $provider->categories()->sync($categoriesCollection->pluck('id'));

        return $provider;
    }

    public function delete(Provider $provider): bool
    {
        try {


            $files = Storage::disk('tenants')->allFiles($provider->directory);
            $directorySize = 0;
            foreach ($files as $file) {
                $directorySize += Storage::disk('tenants')->size($file);
            }

            Company::decrementDiskSize($directorySize);

            Storage::disk('tenants')->deleteDirectory($provider->directory);

            $provider->delete();
            return true;
        } catch (Exception $e) {
            Log::info('Error during provider deletion', ['provider' => $provider, 'error' => $e->getMessage()]);
            return false;
        }
        return false;
    }
}
