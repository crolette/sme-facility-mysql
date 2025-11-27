<?php

namespace App\Services;

use App\Models\Tenants\Contract;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ContractExportImportService
{
    public function generateDataForHash(Contract $contract): array
    {
        return [
            "id" => $contract->id,
            "name" => trim($contract->name),
            "type" => $contract->type?->value,
            "internal_reference" => $contract->internal_reference,
            "provider_reference" => $contract->provider_reference,
            "contract_duration" => $contract->contract_duration?->value,
            "notice_period" => $contract->notice_period?->value,
            "start_date" => $contract->start_date,
            "end_date" => $contract->end_date,
            "renewal_type" => $contract->renewal_type?->value,
            "status" => $contract->status?->value,
            "notes" => trim($contract->notes),
            "provider" => $contract->provider?->name ?? null,
        ];
    }

    public function generateExcelDisplayData(Contract $contract): array
    {
        return [
            "id" => $contract->id,
            "name" => $contract->name,
            "type" => $contract->type?->value,
            "internal_reference" => $contract->internal_reference,
            "provider_reference" => $contract->provider_reference,
            "contract_duration" => $contract->contract_duration?->value,
            "notice_period" => $contract->notice_period?->value,
            "start_date" => $contract->start_date ? Date::dateTimeToExcel($contract->start_date) : null,
            "end_date" => $contract->end_date ? Date::dateTimeToExcel($contract->end_date) : null,
            "renewal_type" => $contract->renewal_type?->value,
            "status" => $contract->status?->value,
            "notes" => $contract->notes,
            "provider" => $contract->provider?->name ?? null,
        ];
    }

    public function calculateHash(array $rowData): string
    {
        ksort($rowData);
        return hash('sha256', json_encode($rowData, JSON_UNESCAPED_UNICODE));
    }
}
