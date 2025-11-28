<?php

namespace App\Exports;

use App\Models\Tenants\Country;
use App\Models\Tenants\Provider;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ContractsDataSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    use RegistersEventListeners;

    private $maxRows;

    public function title(): string
    {
        return 'Datas';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $providers = Provider::select(['id', 'name'])->get();

        // Trouver la longueur max
        $this->maxRows = $providers->count();

        $data = collect();
        for ($i = 0; $i < $this->maxRows; $i++) {
            $data->push([
                $providers[$i]->name,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'providers',
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $spreadsheet = $event->sheet->getParent();
        $dataSheet = $spreadsheet->getSheetByName('Datas');

        $providers = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'providersList',
            $dataSheet,
            '$A$2:$A$500'
        );

        $spreadsheet->addNamedRange($providers);


        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
    }
}
