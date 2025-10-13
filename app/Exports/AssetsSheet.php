<?php

namespace App\Exports;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\Log;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Services\AssetExportImportService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
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

class AssetsSheet implements FromQuery, WithMapping, Responsable, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    use Exportable;
    public function title(): string
    {
        return 'Assets';
    }


    public function query()
    {
        return Asset::query();
        // return Asset::query()->whereIn('id', [37, 49]);
    }

    public function map($asset): array
    {
        $rowData = app(AssetExportImportService::class)->generateDataForHash($asset);
        $hash = app(AssetExportImportService::class)->calculateHash($rowData);
        Log::info($rowData);

        $rowExcel = app(AssetExportImportService::class)->generateExcelDisplayData($asset);
        // Debugbar::info($asset->id, $asset->location);
        return array_merge(array_values($rowExcel), [$hash]);
    }

    public function headings(): array
    {
        return [
            [
                'reference_code',
                'code',
                'name',
                'description',
                'category',
                'need_qr_code',
                'is_mobile',
                'location_type_site',
                'location_type_building',
                'location_type_floor',
                'location_type_room',
                'location_type_user',
                'brand',
                'model',
                'serial_number',
                'depreciable',
                'depreciation_start_date',
                'depreciation_end_date',
                'depreciation_duration',
                'residual_value',
                'surface',
                'purchase_date',
                'purchase_cost',
                'under_warranty',
                'end_warranty_date',
                'need_maintenance',
                'maintenance_frequency',
                'next_maintenance_date',
                'last_maintenance_date',
                'hash'
            ],
            [
                'Reference Code',
                'Code',
                'Name',
                'Description',
                'Category',
                'Need QR Code ?',
                'Is Mobile ? ',
                'Site',
                'Building',
                'Floor',
                'Room',
                'User',
                'Brand',
                'Model',
                'Serial number',
                'Depreciable ?',
                'Depreciation Start date',
                'Depreciation End date',
                'Depreciation duration (years)',
                'Residual value',
                'Surface (m²)',
                'Purchase date',
                'Purchase cost',
                'under_warranty',
                'end_warranty_date',
                'need_maintenance',
                'maintenance_frequency',
                'next_maintenance_date',
                'last_maintenance_date',
                '_hash'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'Q' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'R' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'V' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Y' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AB' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AC' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
        $sheet->freezePane('F3');


        $validation = $sheet->getStyle('C3:Y9999')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $categories = CategoryType::where('category', 'asset')->get()->pluck('label');
        $categoriesList = $categories->join(',');

        // Validation on Category List
        $validation = $sheet->getDataValidation('E3');
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
        $validation->setFormula1('"' . $categoriesList . '"');

        $sheet->setDataValidation('E3:E9999', clone $validation);


        // Boolean on Need Qr Code ?
        $validation = $sheet->getDataValidation('F3');
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
        $validation->setFormula1('"Yes,No"');
        $sheet->setDataValidation('F3:F9999', clone $validation);
        // $validation->setAllowBlank(false);

        // Boolean on Is Mobile ?
        $sheet->setDataValidation('G3:G9999', clone $validation);

        // Depreciable ?
        $sheet->setDataValidation('P3:P9999', clone $validation);

        // Conditional formatting on depreciation_start_date
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($P3="Yes",ISBLANK($Q3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('Q3:Q1000')->setConditionalStyles([$conditional]);

        // Conditional formatting on depreciation_duration
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($P3="Yes",ISBLANK($S3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('S3:S1000')->setConditionalStyles([$conditional]);

        // Under warranty
        $sheet->setDataValidation('X3:X9999', clone $validation);

        // Conditional formatting on end_warranty_date
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($X3="Yes",ISBLANK($X3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('Y3:Y1000')->setConditionalStyles([$conditional]);

        //Need maintenance
        $sheet->setDataValidation('Z3:Z9999', clone $validation);

        // Conditional formatting on maintenance_frequency
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($Z3="Yes",ISBLANK($Z3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('AA3:AA1000')->setConditionalStyles([$conditional]);


        // Site
        $validation = $sheet->getDataValidation('H3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list. Select only the location where the asset belongs.');
        $validation->setFormula1('sites');
        $sheet->setDataValidation('H3:H9999', clone $validation);

        // Building
        $validation->setFormula1('buildings');
        $sheet->setDataValidation('I3:I9999', clone $validation);

        // Floor
        $validation->setFormula1('floors');
        $sheet->setDataValidation('J3:J9999', clone $validation);

        // Room
        $validation->setFormula1('rooms');
        $sheet->setDataValidation('K3:K9999', clone $validation);

        // Users
        $validation->setFormula1('users');
        $sheet->setDataValidation('L3:L9999', clone $validation);

        $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional1->addCondition('AND($G3="No",ISBLANK($H3),ISBLANK($I3),ISBLANK($J3),ISBLANK($K3))');
        $conditional1->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');

        $sheet->getStyle('H3:H1000')->setConditionalStyles([$conditional1]);
        $sheet->getStyle('I3:I1000')->setConditionalStyles([$conditional1]);
        $sheet->getStyle('J3:J1000')->setConditionalStyles([$conditional1]);
        $sheet->getStyle('K3:K1000')->setConditionalStyles([$conditional1]);

        $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional2->addCondition('AND($G3="Yes",ISBLANK($L3))');
        $conditional2->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');

        $sheet->getStyle('L3:L1000')->setConditionalStyles([$conditional2]);

        // Maintenance Frequency
        $frequencies = collect(array_column(MaintenanceFrequency::cases(), 'value'));
        $frequenciesList = $frequencies->join(',');

        $validation = $sheet->getDataValidation('AA3');
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

        $sheet->setDataValidation('AA3:AA9999', clone $validation);



        return [
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true]],

        ];
    }
}
