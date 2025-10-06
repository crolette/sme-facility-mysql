<?php

namespace App\Exports;

use App\Models\Tenants\Asset;
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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class AssetsExport implements FromQuery, WithMapping, Responsable, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    use Exportable;
    private $fileName = 'assets.xlsx';
    

    public function query() {

        return Asset::query();
    }

    public function map($asset): array
    {
        // Debugbar::info($asset->id, $asset->location);
        return [
            $asset->reference_code,
            $asset->code,
            $asset->category,
            $asset->name,
            $asset->description,
            $asset->is_mobile,
            $asset->location->name ?? $asset->location->full_name,
            $asset->location->reference_code ?? $asset->location->email,
            $asset->brand,
            $asset->model,
            $asset->serial_number,
            $asset->depreciable,
            $asset->depreciation_start_date ? Date::dateTimeToExcel($asset->depreciation_start_date) : null,
            $asset->depreciation_end_date ? Date::dateTimeToExcel($asset->depreciation_end_date) : null,
            $asset->depreciation_duration,
            $asset->surface,
        ];
    }

    public function headings(): array
    {
        return [
            'Reference Code',
            'Code',
            'Category',
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
            'Surface',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'M' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'N' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $protection = $sheet->getProtection();
        $protection->setPassword('');
        $protection->setSheet(true);
        $sheet->protectCells('A:A', '');
        $sheet->protectCells('1:1', '');
        $sheet->protectCells('B:B', '');

        $validation = $sheet->getStyle('C2:M9999')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        $categories = CategoryType::where('category', 'asset')->get()->pluck('label');
        $categoriesList = $categories->join(',');

        $validation = $sheet->getDataValidation('C2');
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

        $sheet->setDataValidation('C2:C9999', clone $validation);

     
        



        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],

        ];
    }
}
