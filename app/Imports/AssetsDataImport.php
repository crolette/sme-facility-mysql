<?php

namespace App\Imports;

use Error;
use Exception;
use Carbon\Carbon;
use Maatwebsite\Excel\Row;
use App\Models\Translation;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\Log;
use App\Models\Central\CategoryType;
use App\Services\MaintainableService;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\OnEachRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\AssetExportImportService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssetsDataImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithStartRow, WithValidation
{

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $assetHash = $row['hash'];
            $rowWithoutHash = $row;
            unset($rowWithoutHash['hash']);

            $calculatedHash = app(AssetExportImportService::class)->calculateHash([...$rowWithoutHash]);

            if ($assetHash !== $calculatedHash) {
                $assetData = $this->transformRowForAssetCreation($row);
                $maintainableData = $this->transformRowForMaintainableCreation($row);
                if ($row['reference_code']) {
                    $asset = Asset::where('code', $row['code'])->first();
                    app(AssetService::class)->update($asset, $assetData);
                } else {
                    $asset = app(AssetService::class)->create($assetData);
                }

                $asset = app(AssetService::class)->attachLocationFromImport($asset, $assetData);

                $translation = Translation::where('translatable_type', CategoryType::class)
                    ->where('label', $row['category'])
                    ->whereHasMorph('translatable', [CategoryType::class], function (Builder $query) {
                        $query->where('category', 'asset');
                    })
                    ->first();

                if (!$translation)
                    throw new Exception('Category type not existing');


                $asset->assetCategory()->associate($translation->translatable_id);
                $asset->save();

                app(MaintainableService::class)->updateOrCreate($asset, $maintainableData);
                app(MaintainableService::class)->attachMaintenanceManagerFromImport($asset->maintainable, $maintainableData['maintenance_manager']);

                if ($row['need_qr_code'] === true)
                    app(QRCodeService::class)->createAndAttachQR($asset);
            }
        }
    }

    private function transformRowForAssetCreation($rowData)
    {

        $data = [
            'reference_code' => $rowData['reference_code'] ?? null,
            'code' => $rowData['code'] ?? null,
            'brand' => $rowData['brand'] ?? null,
            'model' => $rowData['model'] ?? null,
            'serial_number' => $rowData['serial_number'] ?? null,
            'surface' => $rowData['surface'] ?? null,
            'is_mobile' => $rowData['is_mobile'],
            'site' => $rowData['location_type_site'],
            'building' => $rowData['location_type_building'],
            'floor' => $rowData['location_type_floor'],
            'room' => $rowData['location_type_room'],
            'user' => $rowData['location_type_user'],
            'depreciable' => $rowData['depreciable'],
            'depreciation_start_date' => $rowData['depreciation_start_date'] ?? null,
            'depreciation_end_date' => $rowData['depreciation_end_date'] ?? null,
            'depreciation_duration' => $rowData['depreciation_duration'] ?? null,
            'residual_value' => $rowData['residual_value'] ?? null,

        ];

        return $data;
    }

    private function transformRowForMaintainableCreation($rowData)
    {
        $data = [
            'name' => $rowData['name'],
            'description' => $rowData['description'],
            'purchase_date' => $rowData['purchase_date'],
            'purchase_cost' => $rowData['purchase_cost'],
            'under_warranty' => $rowData['under_warranty'],
            'end_warranty_date' => $rowData['end_warranty_date'],
            'need_maintenance' => $rowData['need_maintenance'],
            'maintenance_frequency' => $rowData['maintenance_frequency'],
            'next_maintenance_date' => $rowData['next_maintenance_date'],
            'last_maintenance_date' => $rowData['last_maintenance_date'],
            'maintenance_manager' => $rowData['maintenance_manager'],
        ];



        return $data;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function prepareForValidation($data)
    {
        isset($data['need_qr_code']) && ($data['need_qr_code'] === 'Yes') ? $data['need_qr_code'] = true : $data['need_qr_code'] = false;
        isset($data['is_mobile']) && ($data['is_mobile'] === 'Yes') ? $data['is_mobile'] = true : $data['is_mobile'] = false;
        isset($data['depreciable']) && ($data['depreciable'] === 'Yes') ? $data['depreciable'] = true : $data['depreciable'] = false;

        if (isset($data['serial_number'])) {
            $data['serial_number'] = strval($data['serial_number']);
        }

        if ($data['depreciable'] === false) {
            $data['depreciation_start_date'] = null;
            $data['depreciation_end_date'] = null;
            $data['depreciation_duration'] = null;
            $data['residual_value'] = null;
        } else {
            $startDate = Carbon::instance(Date::excelToDateTimeObject($data['depreciation_start_date']));
            $data['depreciation_start_date'] = $startDate->format('Y-m-d');
            $data['depreciation_end_date'] = $startDate->addYears($data['depreciation_duration'])->format('Y-m-d');
        }

        isset($data['under_warranty']) && ($data['under_warranty'] === 'Yes') ? $data['under_warranty'] = true : $data['under_warranty'] = false;
        isset($data['need_maintenance']) && ($data['need_maintenance'] === 'Yes') ? $data['need_maintenance'] = true : $data['need_maintenance'] = false;

        if ($data['need_maintenance'] === true) {
            if (!isset($data['next_maintenance_date'])) {

                if (isset($data['maintenance_frequency']) && $data['maintenance_frequency'] !== MaintenanceFrequency::ONDEMAND->value) {

                    $data['next_maintenance_date'] = isset($data['last_maintenance_date']) ? calculateNextMaintenanceDate($data['maintenance_frequency'], Carbon::instance(Date::excelToDateTimeObject($data['last_maintenance_date']))->toDateString()) : calculateNextMaintenanceDate($data['maintenance_frequency']);
                }
            } else {

                $data['next_maintenance_date'] = Carbon::instance(Date::excelToDateTimeObject($data['next_maintenance_date']))->format('Y-m-d');
            }
        }

        if (isset($data['last_maintenance_date']))
            $data['last_maintenance_date'] = Carbon::instance(Date::excelToDateTimeObject($data['last_maintenance_date']))->format('Y-m-d');

        if ($data['need_maintenance'] === false) {
            $data['next_maintenance_date'] = null;
            $data['last_maintenance_date'] = null;
        }

        if (isset($data['purchase_date'])) {

            $data['purchase_date'] = Carbon::instance(Date::excelToDateTimeObject($data['purchase_date']))->format('Y-m-d');
        }

        if (isset($data['end_warranty_date']))
            $data['end_warranty_date'] = Carbon::instance(Date::excelToDateTimeObject($data['end_warranty_date']))->format('Y-m-d');

        return $data;
    }



    public function rules(): array
    {
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');

        return [
            'reference_code' => ['nullable'],
            'code' => ['nullable', 'exists:assets,code'],
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:50'],
            'depreciable' => "boolean",
            "depreciation_start_date" => 'nullable|date|required_if_accepted:depreciable',
            "depreciation_end_date" => 'nullable|date',
            "depreciation_duration" => 'nullable|required_with:depreciation_start_date|numeric|gt:0',

            'name' => 'required|string|min:4|max:100',
            'description' => 'required|string|min:10|max:255',
            'purchase_date' => ['nullable', 'date', Rule::date()->todayOrBefore()],
            'purchase_cost' => 'nullable|numeric|gt:0|decimal:0,2',
            'under_warranty' => "boolean",
            'end_warranty_date' => [
                'nullable',
                'date',
                'required_if_accepted:under_warranty',
            ],
            'need_maintenance' => "boolean",
            'maintenance_frequency' => ['nullable', 'required_if_accepted:need_maintenance', Rule::in($frequencies)],
            'next_maintenance_date' => ['nullable', 'date'],
            'last_maintenance_date' =>  ['nullable', 'date', Rule::date()->todayOrBefore()],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ('*.under_warranty' === true && '*.under_warranty_date' <= now())
                $validator->errors()->add('under_warranty_date', 'End of warranty date must be in the future.');

            if (!empty('*.purchase_date') && '*.under_warranty_date' <= '*.purchase_date')
                $validator->errors()->add('under_warranty_date', 'End of warranty date must be after purchase date.');
        });
    }
}
