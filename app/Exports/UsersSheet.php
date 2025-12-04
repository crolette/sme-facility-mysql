<?php

namespace App\Exports;

use App\Models\Tenants\User;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Services\UserExportImportService;
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

class UsersSheet implements FromQuery, WithMapping, Responsable, WithEvents, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    use Exportable;
    public function __construct(private array $userIds = [], private $template = false) {}

    public function title(): string
    {
        return 'Contacts';
    }


    public function query()
    {
        $query = User::query();

        if ($this->template) {
            $query->limit(0);
        } else if (!empty($this->userIds)) {
            $query->whereIn('id', $this->userIds);
        }

        return $query;
    }

    public function map($provider): array
    {
        $rowData = app(UserExportImportService::class)->generateDataForHash($provider);
        $hash = app(UserExportImportService::class)->calculateHash($rowData);

        $rowExcel = app(UserExportImportService::class)->generateExcelDisplayData($provider);
        // Debugbar::info($asset->id, $asset->location);
        return array_merge(array_values($rowExcel), [$hash]);
    }

    public function headings(): array
    {
        return [
            [
                "id",
                "first_name",
                "last_name",
                "email",
                "job_position",
                "phone_number",
                'provider',
                'hash'
            ],
            [
                'ID',
                __('common.first_name'),
                __('common.last_name'),
                __('common.email'),
                __('contacts.job_position'),
                __('common.phone'),
                trans_choice('providers.title', 1) . ' ?',
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
        $protection->setPassword('SME_2025!fwebxp');
        $protection->setSheet(true);

        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('G')->setWidth(50);

        $sheet->getStyle('B3:H9999')->getProtection()
            ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $sheet->getRowDimension('1')->setRowHeight(0);
        $sheet->getColumnDimension('A')->setVisible(false);
        $sheet->getColumnDimension('H')->setVisible(false);
        $sheet->freezePane('D3');

        // Validation on Category List
        $validation = $sheet->getDataValidation('G3');
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
        $validation->setFormula1('providersList');

        $sheet->setDataValidation('G3:G1000', clone $validation);

        // Conditional formatting
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($C3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('C3:C1000')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($D3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('D3:D1000')->setConditionalStyles([$conditional]);


        //Validation longueur champs
        $validationLength = $sheet->getDataValidation('B3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
        $validationLength->setFormula1('3');
        $validationLength->setFormula2('255');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir entre 3 et 255 caractères.');
        $sheet->setDataValidation('B3:B9999', clone $validationLength);
        $sheet->setDataValidation('C3:C9999', clone $validationLength);
        $sheet->setDataValidation('D3:D9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('E3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('100');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setAllowBlank(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 100 caractères.');
        $sheet->setDataValidation('E3:E9999', clone $validationLength);

        $validationLength = $sheet->getDataValidation('F3');
        $validationLength->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH);
        $validationLength->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validationLength->setFormula1('16');
        $validationLength->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationLength->setShowErrorMessage(true);
        $validationLength->setAllowBlank(true);
        $validationLength->setErrorTitle('Erreur de longueur');
        $validationLength->setError('Le texte doit contenir max 16 caractères.');
        $sheet->setDataValidation('F3:F9999', clone $validationLength);



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
                    ->getStyle('F:F')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
