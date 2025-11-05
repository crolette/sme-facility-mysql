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
    public function title(): string
    {
        return 'Providers';
    }


    public function query()
    {
        return Provider::query();
        // return Asset::query()->whereIn('id', [37, 49]);
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
                'Name',
                "Email",
                "Website",
                'Category',
                'VAT Number',
                'Phone number',
                'Street',
                'House number',
                'Postal code',
                'City',
                'Country',
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
        $sheet->protectCells('1:1', '');
        $sheet->protectCells('1:2', '');
        $sheet->protectCells('A:A', '');
        $sheet->protectCells('B:M', '');
        $sheet->getRowDimension('1')->setRowHeight(0);
        $sheet->getColumnDimension('M')->setVisible(false);
        $sheet->freezePane('D3');


        $validation = $sheet->getStyle('B3:M9999')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        // $categories = CategoryType::where('category', 'provider')->get()->pluck('label');
        // $categoriesList = $categories->join(',');

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
        $validation->setFormula1('categories');

        $sheet->setDataValidation('E3:E1000', clone $validation);

        // Countries
        $validation->setFormula1('countriesLabels');
        $sheet->setDataValidation('L3:L1000', clone $validation);

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
        $conditional->addCondition('AND($B3<>"",ISBLANK($E3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('E3:E1000')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($G3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('G3:G1000')->setConditionalStyles([$conditional]);


        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($H3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('H3:H1000')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($J3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('J3:J1000')->setConditionalStyles([$conditional]);

        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($K3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('K3:K1000')->setConditionalStyles([$conditional]);


        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition('AND($B3<>"",ISBLANK($L3))');
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
        $sheet->getStyle('L3:L1000')->setConditionalStyles([$conditional]);


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
