<?php

namespace App\Exports;

use App\Models\Tenants\Provider;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\ProviderExportImportService;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ProvidersSheet implements FromQuery, WithMapping, Responsable, WithEvents, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    use Exportable;

    public function __construct(private array $providerIds = [], private $template = false) {}


    public function title(): string
    {
        return 'Providers';
    }


    public function query()
    {
        $query = Provider::query();

        if ($this->template) {
            $query->limit(0);
        } else if (!empty($this->providerIds)) {
            $query->whereIn('id', $this->providerIds);
        }

        return $query;
    }

    public function map($provider): array
    {
        $rowData = app(ProviderExportImportService::class)->generateDataForHash($provider);
        $hash = app(ProviderExportImportService::class)->calculateHash($rowData);

        $rowExcel = app(ProviderExportImportService::class)->generateExcelDisplayData($provider);
        // Debugbar::info($asset->id, $asset->location);
        return array_merge(array_values($rowExcel), [$hash]);
    }

    public function headings(): array
    {
        return [
            [
                "id",
                "name",
                "email",
                "website",
                "category",
                "vat_number",
                "phone_number",
                "street",
                "house_number",
                "postal_code",
                "city",
                "country",
                'hash'
            ],
            [
                'ID',
                __('providers.company_name'),
                __('common.email'),
                __('providers.website'),
                __('common.category'),
                __('providers.vat_number'),
                __('common.phone'),
                __('common.street'),
                __('common.house_number'),
                __('common.postal_code'),
                __('common.city'),
                __('common.country'),
                '_hash'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'G' => DataType::TYPE_STRING2,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $protection = $sheet->getProtection();
        $protection->setPassword('');
        $protection->setSheet(true);
        $protection->setFormatColumns(true);

        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(50);
        $sheet->getColumnDimension('H')->setWidth(50);
        $sheet->getColumnDimension('K')->setWidth(50);
        $sheet->getColumnDimension('L')->setWidth(50);

        $sheet->getStyle('B3:M9999')->getProtection()
            ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $sheet->getRowDimension('1')->setRowHeight(0);
        $sheet->getColumnDimension('M')->setVisible(false);
        $sheet->freezePane('D3');

        // $categories = CategoryType::where('category', 'provider')->get()->pluck('label');
        // $categoriesList = $categories->join(',');

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
        $validation->setFormula1('categories');

        $sheet->setDataValidation('E3:E9999', clone $validation);

        // Countries
        $validation->setFormula1('countriesLabels');
        $sheet->setDataValidation('L3:L9999', clone $validation);

        // Conditional formatting
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($C3)),AND($B3<>"",ISBLANK($C3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('C3:C9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($E3)),AND($B3<>"",ISBLANK($E3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('E3:E9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($G3)),AND($B3<>"",ISBLANK($G3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('G3:G9999')->setConditionalStyles([$conditional]);


        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($H3)),AND($B3<>"",ISBLANK($H3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('H3:H9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($J3)),AND($B3<>"",ISBLANK($J3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('J3:J9999')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($K3)),AND($B3<>"",ISBLANK($K3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('K3:K9999')->setConditionalStyles([$conditional]);


        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('OR(AND($C3<>"",ISBLANK($L3)),AND($B3<>"",ISBLANK($L3)))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('L3:L9999')->setConditionalStyles([$conditional]);

        // Validation longueur de champs
        $validationLength = $sheet->getDataValidation('B3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('4');
        $validationLength->setFormula2('255');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 4 et 255 caractères.');
        $sheet->setDataValidation('B3:B9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('C3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('4');
        $validationLength->setFormula2('255');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 4 et 255 caractères.');
        $sheet->setDataValidation('C3:C9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('F3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('14');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 14 caractères.');
        $sheet->setDataValidation('F3:F9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('G3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('16');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 16 caractères.');
        $sheet->setDataValidation('G3:G9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('H3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('100');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);

        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 100 caractères.');
        $sheet->setDataValidation('H3:H9999', clone $validationLength);


        $validationLength = $sheet->getDataValidation('J3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('8');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 8 caractères.');
        $sheet->setDataValidation('J3:J9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('K3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('100');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);

        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 100 caractères.');
        $sheet->setDataValidation('K3:K9999', clone $validationLength);


        return [
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true]],

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()
                    ->getStyle('G:G')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
