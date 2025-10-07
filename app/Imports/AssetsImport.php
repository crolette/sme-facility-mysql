<?php

namespace App\Imports;

use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Row;
use App\Models\Tenants\Asset;
use App\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssetsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithStartRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        foreach($rows as $row) {
            Log::info($row);
            if(!$row['reference_code']) {
                Log::info('+++ Create new asset : ' . $row['name']);
            } else {
                Log::info('*** Update asset : ' . $row['name'] . ' - ' . $row['reference_code']);
                $asset = Asset::where('reference_code', $row['reference_code'])->first();
                $asset->update(
                    [
                        'brand' => $row['brand'],
                        'model' => $row['model'],
                        'serial_number' => $row['serial_number']
                    ]
                );

                // Search the category type based on the localized label selected in the excel file
                $translation = Translation::where('translatable_type', (CategoryType::class))->where('label', $row['category'])->first();
                $asset->assetCategory()->associate($translation->translatable->id);
                

                $asset->maintainable->update(
                    [
                        'name' => $row['name'],
                        'description' => $row['description']
                    ]
                );

                $asset->save();
            }

        }
        
    }

    public function startRow(): int
    {
        return 3;
    }

    public function prepareForValidation($data, $index)
    {
        isset($data['need_qr_code']) && ($data['need_qr_code'] === 'Yes') ? $data['need_qr_code'] = true : $data['need_qr_code'] = false;
        isset($data['is_mobile']) && ($data['is_mobile'] === 'Yes') ? $data['is_mobile'] = true : $data['is_mobile'] = false;

        return $data;
    }

 

    public function rules(): array
    {
        return [
            'reference_code' => ['nullable'],
            'code' => ['nullable'],
            'name' => 'required|string|min:4|max:100',
            'description' => 'nullable|string|min:10|max:255',
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
