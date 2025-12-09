<?php

namespace App\Exports;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Enums\MaintenanceFrequency;
use App\Enums\MeterReadingsUnits;
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

class AssetsSheet implements FromQuery, WithMapping, Responsable, WithHeadings, WithStyles, WithColumnFormatting, WithTitle
{
    use Exportable;

    public function __construct(private array $assetIds = [], private $template = false) {}

    public function title(): string
    {
        return 'Assets';
    }


    public function query()
    {
        $query = Asset::query();

        if ($this->template) {
            $query->limit(0);
        } else if (!empty($this->assetIds)) {
            $query->whereIn('id', $this->assetIds);
        }

        return $query;
    }

    public function map($asset): array
    {
        $rowData = app(AssetExportImportService::class)->generateDataForHash($asset);
        $hash = app(AssetExportImportService::class)->calculateHash($rowData);

        $rowExcel = app(AssetExportImportService::class)->generateExcelDisplayData($asset);
        Log::info($rowExcel);
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

                'has_meter_readings',
                'meter_number',
                'meter_unit',

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
                'maintenance_manager',
                'hash'
            ],
            [
                __('common.reference_code'),
                __('common.code'),
                __('common.name'),
                __('common.description'),
                __('common.category'),
                __('assets.need_qr_code') . ' ?',
                __('assets.mobile_asset') . ' ?',
                trans_choice('locations.sites', 1),
                trans_choice('locations.buildings', 1),
                trans_choice('locations.floors', 1),
                trans_choice('locations.rooms', 1),
                __('assets.mobile_asset_user'),
                __('assets.brand'),
                __('assets.model'),
                __('assets.serial_number'),

                __('assets.has_meter_readings') . ' ?',
                __('assets.meter_number'),
                __('assets.meter_readings.unit'),

                __('assets.depreciable') . ' ?',
                __('assets.depreciation_start_date'),
                __('assets.depreciation_end_date'),
                __('assets.depreciation_duration'),
                __('assets.residual_value'),
                __('common.surface') . ' (m²)',
                __('assets.purchase_date'),
                __('assets.purchase_cost'),
                __('assets.still_under_warranty') . ' ?',
                __('assets.warranty_end_date'),
                __('maintenances.need_maintenance') . ' ?',
                __('maintenances.frequency.title'),
                __('maintenances.next_maintenance_date'),
                __('maintenances.last_maintenance_date'),
                __('maintenances.maintenance_manager'),
                '_hash'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'T' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Y' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Z' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AB' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AE' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AF' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $protection = $sheet->getProtection();
        $protection->setPassword('SME_2025!fwebxp');
        $protection->setSheet(true);

        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(40);
        $sheet->getColumnDimension('I')->setWidth(40);
        $sheet->getColumnDimension('J')->setWidth(40);
        $sheet->getColumnDimension('K')->setWidth(40);
        $sheet->getColumnDimension('L')->setWidth(40);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(15);
        $sheet->getColumnDimension('Q')->setWidth(25);
        $sheet->getColumnDimension('R')->setWidth(15);
        $sheet->getColumnDimension('S')->setWidth(15);
        $sheet->getColumnDimension('T')->setWidth(30);
        $sheet->getColumnDimension('U')->setWidth(30);
        $sheet->getColumnDimension('V')->setWidth(30);
        $sheet->getColumnDimension('W')->setWidth(20);
        $sheet->getColumnDimension('X')->setWidth(20);
        $sheet->getColumnDimension('Y')->setWidth(20);
        $sheet->getColumnDimension('Z')->setWidth(20);
        $sheet->getColumnDimension('AA')->setWidth(25);
        $sheet->getColumnDimension('AB')->setWidth(25);
        $sheet->getColumnDimension('AC')->setWidth(30);
        $sheet->getColumnDimension('AD')->setWidth(30);
        $sheet->getColumnDimension('AE')->setWidth(30);
        $sheet->getColumnDimension('AF')->setWidth(30);
        $sheet->getColumnDimension('AG')->setWidth(30);


        $sheet->getStyle('C3:AD9999')->getProtection()
            ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $sheet->getRowDimension('1')->setRowHeight(0);
        $sheet->getColumnDimension('AH')->setVisible(false);
        $sheet->freezePane('E3');

        $categories = CategoryType::where('category', 'asset')->get()->pluck('label');
        $categoriesList = $categories->join(',');


        // Validation on Category List
        $validation = $sheet->getDataValidation('E3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
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
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
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

        // Has meter readings
        $sheet->setDataValidation('P3:P9999', clone $validation);

        // Depreciable ?
        $sheet->setDataValidation('S3:S9999', clone $validation);



        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($C3)),AND($B3<>"",ISBLANK($C3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('C3:C9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($D3)),AND($B3<>"",ISBLANK($D3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('D3:D9999')->setConditionalStyles([$conditional]);


        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($E3)),AND($B3<>"",ISBLANK($E3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('E3:E9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($F3)),AND($B3<>"",ISBLANK($F3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('F3:F9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($G3)),AND($B3<>"",ISBLANK($G3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('G3:G9999')->setConditionalStyles([$conditional]);


        // Conditional formatting on depreciation_start_date
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($S3="Yes",ISBLANK($T3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('T3:T9999')->setConditionalStyles([$conditional]);

        // Conditional formatting on depreciation_duration
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($S3="Yes",ISBLANK($V3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('V3:V9999')->setConditionalStyles([$conditional]);

        // Under warranty
        $sheet->setDataValidation('AA3:AA9999', clone $validation);

        // Conditional formatting on end_warranty_date
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($AA3="Yes",ISBLANK($AA3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('AB3:AB9999')->setConditionalStyles([$conditional]);

        //Need maintenance
        $sheet->setDataValidation('AC3:AC9999', clone $validation);

        // Conditional formatting on maintenance_frequency
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($AC3="Yes",ISBLANK($AC3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('AD3:AD9999')->setConditionalStyles([$conditional]);


        // Site
        $validation = $sheet->getDataValidation('H3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
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
        $sheet->setDataValidation('AG3:AG9999', clone $validation);

        $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional1->addCondition('AND($G3="No",ISBLANK($H3),ISBLANK($I3),ISBLANK($J3),ISBLANK($K3))');
        $conditional1->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');

        $sheet->getStyle('H3:H9999')->setConditionalStyles([$conditional1]);
        $sheet->getStyle('I3:I9999')->setConditionalStyles([$conditional1]);
        $sheet->getStyle('J3:J9999')->setConditionalStyles([$conditional1]);
        $sheet->getStyle('K3:K9999')->setConditionalStyles([$conditional1]);

        $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional2->addCondition('AND($G3="Yes",ISBLANK($L3))');
        $conditional2->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');

        $sheet->getStyle('L3:L9999')->setConditionalStyles([$conditional2]);

        // Maintenance Frequency
        $frequencies = collect(array_column(MaintenanceFrequency::cases(), 'value'));
        $frequenciesList = $frequencies->join(',');

        $validation = $sheet->getDataValidation('AD3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('"' . $frequenciesList . '"');

        $sheet->setDataValidation('AD3:AD9999', clone $validation);

        // Meter units
        $meterUnits = collect(array_column(MeterReadingsUnits::cases(), 'value'));
        $meterUnitsList = $meterUnits->join(',');

        $validation = $sheet->getDataValidation('R3');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('"' . $meterUnitsList . '"');

        $sheet->setDataValidation('R3:R9999', clone $validation);

        // Validation longueur de champs
        $validationLength = $sheet->getDataValidation('C3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('4');   // min
        $validationLength->setFormula2('100'); // max
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 4 et 100 caractères.');
        $sheet->setDataValidation('C3:C9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('D3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('10');   // min
        $validationLength->setFormula2('255'); // max
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 10 et 255 caractères.');
        $sheet->setDataValidation('D3:D9999', clone $validationLength);



        $validationLength = $sheet->getDataValidation('M3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('100');
        $validationLength->setAllowBlank(true);
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 100 caractères.');
        $sheet->setDataValidation('M3:M9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('N3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('100');
        $validationLength->setAllowBlank(true);
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 100 caractères.');
        $sheet->setDataValidation('N3:N9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('O3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('50');
        $validationLength->setAllowBlank(true);
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 50 caractères.');
        $sheet->setDataValidation('O3:O9999', clone $validationLength);


        return [
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true]],

        ];
    }
}
