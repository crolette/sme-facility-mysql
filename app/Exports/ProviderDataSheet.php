<?php

namespace App\Exports;

use App\Models\Central\CategoryType;
use App\Models\Tenants\Country;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ProviderDataSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    use RegistersEventListeners;

    public function title(): string
    {
        return 'Datas';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $countries = Country::select(['id', 'iso_code'])->get();
        $categories = CategoryType::where('category', 'provider')->get()->pluck('label');

        // Trouver la longueur max
        $maxRows = max($countries->count(), $categories->count());

        $data = collect();
        for ($i = 0; $i < $maxRows; $i++) {
            $data->push([
                $countries[$i]->label,
                $countries[$i]->iso_code,
                $categories->get($i, ''),
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'countries',
            'countries_codes',
            'categories',
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        // if ($event->sheet->getTitle() === 'Datas') {
        $spreadsheet = $event->sheet->getParent();
        // \Log::info($spreadsheet);
        $dataSheet = $spreadsheet->getSheetByName('Datas');
        // \Log::info($dataSheet);

        // Syntaxe alternative plus explicite
        $countriesLabels = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'countriesLabels',
            $dataSheet,
            '$A$2:$A$195'
        );

        $spreadsheet->addNamedRange($countriesLabels);

        $countries = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'countries',
            $dataSheet,
            '$A$2:$B$195'
        );

        $spreadsheet->addNamedRange($countries);

        $categories = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'categories',
            $dataSheet,
            '$C$2:$C$50'
        );

        $spreadsheet->addNamedRange($categories);
    }
}
