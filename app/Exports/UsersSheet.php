<?php

namespace App\Exports;

use App\Models\Tenants\Provider;
use App\Models\Tenants\User;
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
use App\Services\UserExportImportService;
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
    public function title(): string
    {
        return 'Users';
    }


    public function query()
    {
        return User::query();
        // return Asset::query()->whereIn('id', [37, 49]);
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
                'id',
                'First Name',
                'Last Name',
                "Email",
                "Job Position",
                "Phone number",
                'Provider',
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
        // $protection = $sheet->getProtection();
        // $protection->setPassword('');
        // $protection->setSheet(true);
        $sheet->protectCells('1:1', '');
        $sheet->protectCells('1:2', '');
        $sheet->protectCells('A:A', '');
        $sheet->protectCells('B:H', '');
        $sheet->getRowDimension('1')->setRowHeight(0);
        // $sheet->getColumnDimension('A')->setVisible(false);
        // $sheet->getColumnDimension('H')->setVisible(false);
        $sheet->freezePane('D3');


        $validation = $sheet->getStyle('B3:H9999')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        // Validation on Category List
        $validation = $sheet->getDataValidation('G3');
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
