<?php

namespace App\Exports;

use App\Models\Tenants\Asset;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class AssetsExport implements FromQuery, WithMapping, Responsable, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    use Exportable;
    private $fileName = 'assets.xlsx';
    

    public function query() {
        return Asset::query()->whereIn('id', [37,49]);
    }

    public function map($asset): array
    {
        // Debugbar::info($asset->id, $asset->location);
        return [
            $asset->reference_code,
            $asset->code,
            $asset->category,
            $asset->qr_code ? 'Yes' : 'No',
            $asset->name,
            $asset->description,
            $asset->is_mobile ? 'Yes' : 'No',
            $asset->location->name ?? $asset->location->full_name,
            $asset->location->reference_code ?? $asset->location->email,
            $asset->brand,
            $asset->model,
            $asset->serial_number,
            $asset->depreciable ? 'Yes' : 'No',
            $asset->depreciation_start_date ? Date::dateTimeToExcel($asset->depreciation_start_date) : null,
            $asset->depreciation_end_date ? Date::dateTimeToExcel($asset->depreciation_end_date) : null,
            $asset->depreciation_duration,
            $asset->surface,
            $asset->maintainable->purchase_date ? Date::dateTimeToExcel($asset->maintainable->purchase_date) : null,
            $asset->maintainable->purchase_cost ?? null,
            $asset->maintainable->under_warranty ? 'Yes' : 'No',
            $asset->maintainable->end_warranty_date ? Date::dateTimeToExcel($asset->maintainable->end_warranty_date) : null,
            $asset->maintainable->need_maintenance ? 'Yes' : 'No',
            $asset->maintainable->maintenance_frequency ?? null,
            $asset->maintainable->next_maintenance_date ? Date::dateTimeToExcel($asset->maintainable->next_maintenance_date) : null,
            $asset->maintainable->last_maintenance_date ? Date::dateTimeToExcel($asset->maintainable->last_maintenance_date) : null,
        ];
    }

    public function headings(): array
    {
        return [
            [
                'reference_code', 
                'code', 
                'category', 
                'need_qr_code', 
                'name', 
                'description', 
                'is_mobile', 
                'location_name', 
                'location_code', 
                'brand',
                'model', 
                'serial_number', 
                'depreciable',
                'depreciation_start_date', 
                'depreciation_end_date', 
                'depreciation_duration',
                'surface', 
                'purchase_date', 
                'purchase_cost', 
                'under_warranty', 
                'end_warranty_date', 
                'need_maintenance', 
                'maintenance_frequency', 
                'next_maintenance_date', 
                'last_maintenance_date', 
            ],
            [
                'Reference Code',
                'Code',
                'Category',
                'Need QR Code ?',
                'Name',
                'Description',
                'Is Mobile ? ',
                'Location name',
                'Location code',
                'Brand',
                'Model',
                'Serial number',
                'Depreciable ?',
                'Depreciation Start date',
                'Depreciation End date',
                'Depreciation duration (years)',
                'Surface (m²)',
                'Purchase date',
                'Purchase cost',
                'under_warranty', 
                'end_warranty_date', 
                'need_maintenance',
                'maintenance_frequency', 
                'next_maintenance_date', 
                'last_maintenance_date', 
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'N' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'O' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'R' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'X' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Y' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // $protection = $sheet->getProtection();
        // $protection->setPassword('');
        // $protection->setSheet(true);
        // $sheet->protectCells('1:1', '');
        // $sheet->protectCells('2:2', '');
        // $sheet->protectCells('A:A', '');
        // $sheet->protectCells('B:B', '');
        // $sheet->getRowDimension('1')->setRowHeight(0);
        $sheet->freezePane('A3');
        

        $validation = $sheet->getStyle('C3:Y9999')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $categories = CategoryType::where('category', 'asset')->get()->pluck('label');
        $categoriesList = $categories->join(',');

        // Validation on Category List
        $validation = $sheet->getDataValidation('C3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('"'.$categoriesList.'"');

        $sheet->setDataValidation('C3:C9999', clone $validation);


        // Boolean on Need Qr Code ?
        $validation = $sheet->getDataValidation('D3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"Yes,No"');
        $sheet->setDataValidation('D3:D9999', clone $validation);
        // $validation->setAllowBlank(false);

        // Depreciable ?
        $sheet->setDataValidation('M3:M9999', clone $validation);

        // Boolean on Is Mobile ?
        $sheet->setDataValidation('G3:G9999', clone $validation);
        // Under warranty
        $sheet->setDataValidation('T3:T9999', clone $validation);
        //Need maintenance
        $sheet->setDataValidation('V3:V9999', clone $validation);


        $frequencies = collect(array_column(MaintenanceFrequency::cases(), 'value'));
        $frequenciesList = $frequencies->join(',');

        $validation = $sheet->getDataValidation('W3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('"' . $frequenciesList . '"');

        $sheet->setDataValidation('W3:W9999', clone $validation);



        return [
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true]],

        ];
    }
}
