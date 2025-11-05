<?php

namespace App\Services;

use App\Models\Tenants\Country;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;

class ProviderService
{
    public function create(array $data): Provider
    {

        $provider = new Provider([...$data]);

        $provider = $this->associateCountry($provider, $data['country_code']);
        $provider =  $this->associateCategory($provider, $data['categoryId']);

        $provider->save();
        return $provider;
    }

    public function update(Provider $provider, array $data): Provider
    {
        Log::info('PROVIDERSERVICE UPDATE');
        Log::info($data);
        Log::info($provider->street);

        $provider->update([...$data]);

        Log::info($provider->street);

        $provider = $this->associateCountry($provider, $data['country_code']);
        $provider =  $this->associateCategory($provider, $data['categoryId']);

        $provider->save();

        Log::info('PROVIDER SAVED');
        Log::info($provider->street);

        return $provider;
    }

    private function associateCountry($provider, $countryCode)
    {
        $country = Country::where('iso_code', $countryCode)->first();
        $provider->country()->associate($country);
        return $provider;
    }

    private function associateCategory($provider, $categoryId)
    {
        $provider->providerCategory()->associate($categoryId);
        return $provider;
    }
}
