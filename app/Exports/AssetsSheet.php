<?php

namespace App\Exports;

use App\Models\Tenants\Site;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Room;
use App\Models\Tenants\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        return Asset::query()->whereIn('id', [37, 49]);
    }

    public function map($asset): array
    {
        // Debugbar::info($asset->id, $asset->location);
        return [
            $asset->reference_code,
            $asset->code,
            $asset->name,
            $asset->description,
            $asset->category,
            $asset->qr_code ? 'Yes' : 'No',
            $asset->is_mobile ? 'Yes' : 'No',
            $asset->location_type === Site::class ? 'SITE' : null,
            $asset->location_type === Building::class ? 'BUILDING' : null,
            $asset->location_type === Floor::class ? 'FLOOR' : null,
            $asset->location_type === Room::class ? $asset->location->reference_code . ' - ' . $asset->location->name : null,
            $asset->location_type === User::class ? $asset->location->full_name . ' - ' . $asset->location->email : null,
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
                'name',
                'description',
                'category',
                'need_qr_code',
                'is_mobile',
                'site',
                'building',
                'floor',
                'room',
                'user',
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
            'Q' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'R' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'X' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AA' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AB' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
        $validation->setFormula1('"' . $categoriesList . '"');

        $sheet->setDataValidation('C3:C9999', clone $validation);


        // Boolean on Need Qr Code ?
        $validation = $sheet->getDataValidation('D3');
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
        $sheet->setDataValidation('D3:D9999', clone $validation);
        // $validation->setAllowBlank(false);

        // Boolean on Is Mobile ?
        $sheet->setDataValidation('G3:G9999', clone $validation);

        // Depreciable ?
        $sheet->setDataValidation('O3:O9999', clone $validation);

        // Under warranty
        $sheet->setDataValidation('V3:V9999', clone $validation);
        //Need maintenance
        $sheet->setDataValidation('X3:X9999', clone $validation);


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
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('sites');
        $sheet->setDataValidation('H3:H9999', clone $validation);

        $validation->setFormula1('buildings');
        $sheet->setDataValidation('I3:I9999', clone $validation);

        $validation->setFormula1('floors');
        $sheet->setDataValidation('J3:J9999', clone $validation);

        $validation->setFormula1('rooms');
        $sheet->setDataValidation('K3:K9999', clone $validation);

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

        $frequencies = collect(array_column(MaintenanceFrequency::cases(), 'value'));
        $frequenciesList = $frequencies->join(',');

        $validation = $sheet->getDataValidation('Z3');
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

        $sheet->setDataValidation('Z3:Z9999', clone $validation);



        return [
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true]],

        ];
    }

    
}
