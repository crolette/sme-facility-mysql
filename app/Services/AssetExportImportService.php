<?php

namespace App\Services;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AssetExportImportService
{
    public function generateDataForHash(Asset $asset): array
    {
        return [
            "reference_code" => $asset->reference_code,
            "code" => $asset->code,
            "name" => trim($asset->name),
            "description" => trim($asset->description),
            "category" => $asset->category,
            "need_qr_code" => $asset->qr_code
                ? true
                : false,
            "is_mobile" => $asset->is_mobile,
            "location_type_site" => $asset->location_type === Site::class
                ? $asset->location->reference_code . ' - ' . $asset->location->name
                : null,
            "location_type_building" => $asset->location_type === Building::class
                ? $asset->location->reference_code . ' - ' . $asset->location->name
                : null,
            "location_type_floor" => $asset->location_type === Floor::class
                ? $asset->location->reference_code . ' - ' . $asset->location->name
                : null,
            "location_type_room" => $asset->location_type === Room::class
                ? $asset->location->reference_code . ' - ' . $asset->location->name
                : null,
            "location_type_user" => $asset->location_type === User::class
                ? $asset->location->full_name . ' - ' . $asset->location->email
                : null,
            "brand" => $asset->brand,
            "model" => $asset->model,
            "serial_number" => $asset->serial_number,
            "has_meter_readings" => $asset->has_meter_readings,
            "meter_number" => $asset->meter_number,
            "meter_unit" =>  $asset->meter_unit?->value ?? null,
            "depreciable" => $asset->depreciable,
            "depreciation_start_date" => $asset->depreciation_start_date
                ? $asset->depreciation_start_date->format('Y-m-d')
                : null,
            "depreciation_end_date" => $asset->depreciation_end_date
                ? $asset->depreciation_end_date->format('Y-m-d')
                : null,
            "depreciation_duration" => $asset->depreciation_duration,
            "residual_value" => $asset->residual_value
                ? (float) $asset->residual_value
                : null,
            "surface" => $asset->surface
                ? (float) $asset->surface
                : null,
            "purchase_date" => $asset->maintainable->purchase_date
                ? $asset->maintainable->purchase_date->format('Y-m-d')
                : null,
            "purchase_cost" => $asset->maintainable->purchase_cost
                ? (float) $asset->maintainable->purchase_cost
                : null,
            "under_warranty" => $asset->maintainable->under_warranty,
            'end_warranty_date' => $asset->maintainable->end_warranty_date
                ? $asset->maintainable->end_warranty_date->format('Y-m-d')
                : null,
            "need_maintenance" => $asset->maintainable->need_maintenance,
            "maintenance_frequency" => $asset->maintainable->maintenance_frequency,
            "next_maintenance_date" => $asset->maintainable->next_maintenance_date
                ? $asset->maintainable->next_maintenance_date->format('Y-m-d')
                : null,
            "last_maintenance_date" => $asset->maintainable->last_maintenance_date
                ? $asset->maintainable->last_maintenance_date->format('Y-m-d')
                : null,
            "maintenance_manager" => $asset->manager ? $asset->manager->full_name . ' - ' . $asset->manager->email : null,
        ];
    }

    public function generateExcelDisplayData(Asset $asset): array
    {
        return [
            "reference_code" => $asset->reference_code,
            "code" => $asset->code,
            "name" => trim($asset->name),
            "description" => trim($asset->description),
            "category" => $asset->category,
            "need_qr_code" => $asset->qr_code ? 'Yes' : 'No',
            "is_mobile" => $asset->is_mobile ? 'Yes' : 'No',
            "location_type_site" => $asset->location_type === Site::class ? $asset->location->reference_code . ' - ' . $asset->location->name : null,
            "location_type_building" => $asset->location_type === Building::class ? $asset->location->reference_code . ' - ' . $asset->location->name : null,
            "location_type_floor" => $asset->location_type === Floor::class ? $asset->location->reference_code . ' - ' . $asset->location->name : null,
            "location_type_room" => $asset->location_type === Room::class ? $asset->location->reference_code . ' - ' . $asset->location->name : null,
            "location_type_user" => $asset->location_type === User::class ? $asset->location->full_name . ' - ' . $asset->location->email : null,
            "brand" => $asset->brand,
            "model" => $asset->model,
            "serial_number" => $asset->serial_number,
            "has_meter_readings" => $asset->has_meter_readings ? 'Yes' : 'No',
            "meter_number" => $asset->meter_number,
            "meter_unit" =>  $asset->meter_unit?->value ?? null,
            "depreciable" => $asset->depreciable ? 'Yes' : 'No',
            "depreciation_start_date" => $asset->depreciation_start_date ? Date::dateTimeToExcel($asset->depreciation_start_date) : null,
            "depreciation_end_date" => $asset->depreciation_end_date ? Date::dateTimeToExcel($asset->depreciation_end_date) : null,
            "depreciation_duration" => $asset->depreciation_duration,
            "residual_value" => $asset->residual_value  ? (float) $asset->residual_value : null,
            "surface" => $asset->surface ? (float) $asset->surface : null,
            "purchase_date" => $asset->maintainable->purchase_date ? Date::dateTimeToExcel($asset->maintainable->purchase_date) : null,
            "purchase_cost" => $asset->maintainable->purchase_cost ? (float) $asset->maintainable->purchase_cost : null,
            "under_warranty" => $asset->maintainable->under_warranty ? 'Yes' : 'No',
            'end_warranty_date' => $asset->maintainable->end_warranty_date ? Date::dateTimeToExcel($asset->maintainable->end_warranty_date) : null,
            "need_maintenance" => $asset->maintainable->need_maintenance ? 'Yes' : 'No',
            "maintenance_frequency" => $asset->maintainable->maintenance_frequency ?? null,
            "next_maintenance_date" => $asset->maintainable->next_maintenance_date ? Date::dateTimeToExcel($asset->maintainable->next_maintenance_date) : null,
            "last_maintenance_date" => $asset->maintainable->last_maintenance_date ? Date::dateTimeToExcel($asset->maintainable->last_maintenance_date) : null,
            "maintenance_manager" => $asset->manager ? $asset->manager->full_name . ' - ' . $asset->manager->email : null,
        ];
    }

    public function calculateHash(array $rowData): string
    {
        ksort($rowData);
        return hash('sha256', json_encode($rowData, JSON_UNESCAPED_UNICODE));
    }
}
