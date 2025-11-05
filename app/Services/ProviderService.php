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
        $provider->update([...$data]);

        $provider = $this->associateCountry($provider, $data['country_code']);
        $provider =  $this->associateCategory($provider, $data['categoryId']);

        $provider->save();

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
