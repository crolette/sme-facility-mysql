<?php

namespace App\Services;

use App\Models\Tenants\Provider;

class ProviderExportImportService
{
    public function generateDataForHash(Provider $provider): array
    {
        return [
            "id" => $provider->id,
            "name" => $provider->name,
            "email" => $provider->email,
            "website" => $provider->website,
            "category_1" => $provider->categories[0]->label ?? '',
            "category_2" => $provider->categories[1]->label ?? '',
            "category_3" => $provider->categories[2]?->label ?? '',
            "vat_number" => $provider->vat_number,
            "phone_number" => $provider->phone_number,
            "street" => $provider->street,
            "house_number" => $provider->house_number,
            "postal_code" => $provider->postal_code,
            "city" => $provider->city,
        ];
    }

    public function generateExcelDisplayData(Provider $provider): array
    {
        return [
            "id" => $provider->id,
            "name" => $provider->name,
            "email" => $provider->email,
            "website" => $provider->website,
            "category_1" => $provider->categories[0]->label ?? '',
            "category_2" => $provider->categories[1]->label ?? '',
            "category_3" => $provider->categories[2]?->label ?? '',
            "vat_number" => $provider->vat_number,
            "phone_number" => str_replace('+', '00', $provider->phone_number),
            "street" => $provider->street,
            "house_number" => $provider->house_number,
            "postal_code" => $provider->postal_code,
            "city" => $provider->city,
            "country" => $provider->country->label,
        ];
    }

    public function calculateHash(array $rowData): string
    {
        ksort($rowData);
        return hash('sha256', json_encode($rowData, JSON_UNESCAPED_UNICODE));
    }
}
