<?php

namespace App\Services;

use App\Models\Tenants\Provider;
use App\Models\Tenants\User;

class UserExportImportService
{
    public function generateDataForHash(User $user): array
    {
        return [
            "id" => $user->id ?? null,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "job_position" => $user->job_position,
            "phone_number" => $user->phone_number,
            "provider_id" => $user->provider->id ?? null,
        ];
    }

    public function generateExcelDisplayData(User $user): array
    {
        return [
            "id" => $user->id,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "job_position" => $user->job_position,
            "phone_number" => str_replace('+', '00', $user->phone_number),
            "provider" => $user->provider->name ?? '',
        ];
    }

    public function calculateHash(array $rowData): string
    {
        ksort($rowData);
        return hash('sha256', json_encode($rowData, JSON_UNESCAPED_UNICODE));
    }
}
