<?php

namespace App\Exports;

use App\Enums\NoticePeriodEnum;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use Illuminate\Support\Facades\Log;
use App\Enums\ContractRenewalTypesEnum;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\ContractExportImportService;
use App\Services\ProviderExportImportService;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ContractsSheet implements FromQuery, WithMapping, Responsable, ShouldAutoSize, WithHeadings, WithStyles, WithColumnFormatting, WithTitle
{
    use Exportable;

    public function __construct(private array $contractIds = [], private $template = false) {}


    public function title(): string
    {
        return 'Contracts';
    }


    public function query()
    {
        $query = Contract::query();

        if ($this->template) {
            $query->limit(0);
        } else if (!empty($this->contractIds)) {
            $query->whereIn('id', $this->contractIds);
        }

        return $query;
    }

    public function map($contract): array
    {
        $rowData = app(ContractExportImportService::class)->generateDataForHash($contract);
        $hash = app(ContractExportImportService::class)->calculateHash($rowData);

        $rowExcel = app(ContractExportImportService::class)->generateExcelDisplayData($contract);
        // Debugbar::info($asset->id, $asset->location);
        return array_merge(array_values($rowExcel), [$hash]);
    }

    public function headings(): array
    {
        return [
            [
                "id",
                "name",
                "type",
                "internal_reference",
                "provider_reference",
                "contract_duration",
                "notice_period",
                "start_date",
                "end_date",
                "renewal_type",
                "status",
                "notes",
                "provider",
                'hash'
            ],
            [
                'ID',
                __('common.name'),
                __('common.type'),
                __('contracts.internal_ref'),
                __('contracts.provider_ref'),
                __('contracts.duration_contract'),
                __('contracts.duration_notice'),
                __('contracts.start_date'),
                __('contracts.end_date'),
                __('contracts.renewal_type'),
                __('common.status'),
                __('common.notes'),
                __('providers.company_name'),
                '_hash'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $protection = $sheet->getProtection();
        $protection->setSheet(true);
        $protection->setPassword('');

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(50);
        $sheet->getColumnDimension('M')->setWidth(25);

        $sheet->getStyle('B3:M9999')->getProtection()
            ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $sheet->getRowDimension('1')->setRowHeight(0);
        $sheet->getColumnDimension('N')->setVisible(false);
        $sheet->freezePane('D3');

        $contractTypes = collect(array_column(ContractTypesEnum::cases(), 'value'));
        $contractTypes = $contractTypes->join(',');

        $validation = $sheet->getDataValidation('C3');
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
        $validation->setFormula1('"' . $contractTypes . '"');

        $sheet->setDataValidation('C3:C9999', clone $validation);

        $contractDuration = collect(array_column(ContractDurationEnum::cases(), 'value'));
        $contractDuration = $contractDuration->join(',');
        $validation->setFormula1('"' . $contractDuration . '"');
        $sheet->setDataValidation('F3:F9999', clone $validation);

        $noticePeriodDuration = collect(array_column(NoticePeriodEnum::cases(), 'value'));
        $noticePeriodDuration = $noticePeriodDuration->join(',');
        $validation->setFormula1('"' . $noticePeriodDuration . '"');
        $sheet->setDataValidation('G3:G9999', clone $validation);

        $renewalTypes = collect(array_column(ContractRenewalTypesEnum::cases(), 'value'));
        $renewalTypes = $renewalTypes->join(',');
        $validation->setFormula1('"' . $renewalTypes . '"');
        $sheet->setDataValidation('J3:J9999', clone $validation);

        $contractStatuses = collect(array_column(ContractStatusEnum::cases(), 'value'));
        $contractStatuses = $contractStatuses->join(',');
        $validation->setFormula1('"' . $contractStatuses . '"');
        $sheet->setDataValidation('K3:K9999', clone $validation);

        $validation->setFormula1('providersList');
        $sheet->setDataValidation('M3:M9999', clone $validation);


        // Conditional formatting
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($A3<>"",ISBLANK($B3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('B3:B9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($A3<>"",ISBLANK($C3)),AND($B3<>"",ISBLANK($C3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('C3:C9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($A3<>"",ISBLANK($J3)),AND($B3<>"",ISBLANK($J3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('J3:J9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($A3<>"",ISBLANK($K3)),AND($B3<>"",ISBLANK($K3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('K3:K9999')->setConditionalStyles([$conditional]);


        // Validation longueur de champs
        $validationLength = $sheet->getDataValidation('B3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('4');
        $validationLength->setFormula2('100');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 4 et 100 caractères.');
        $sheet->setDataValidation('B3:B9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('D3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('50');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 14 caractères.');
        $sheet->setDataValidation('D3:D9999', clone $validationLength);
        $sheet->setDataValidation('E3:E9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('L3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('4');
        $validationLength->setFormula2('250');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 4 et 250 caractères.');
        $sheet->setDataValidation('L3:L9999', clone $validationLength);





        return [
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true]],

        ];
    }

    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             $sheet = $event->sheet->getDelegate();

    //             // D'abord déverrouiller les cellules éditables
    //             $sheet->getStyle('C3:AD9999')->getProtection()
    //                 ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

    //             // Ensuite activer la protection
    //             $protection = $sheet->getProtection();
    //             $protection->setSheet(true);
    //             $protection->setPassword('');
    //             $protection->setFormatColumns(true);
    //             $protection->setFormatCells(true);

    //             // Ton autre code
    //             $sheet->getRowDimension('1')->setRowHeight(0);
    //             $sheet->getColumnDimension('AE')->setVisible(false);
    //             $sheet->freezePane('F3');
    //         },
    //     ];
    // }
}
