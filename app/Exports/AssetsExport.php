<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class AssetsExport implements WithMultipleSheets, WithEvents
{
    use RegistersEventListeners;

    public function sheets() : array
    {
        return [
            'Assets' => new AssetsSheet(),
            'Datas' => new AssetDataSheet()
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        \Log::info('AFTER SHEET');
        \Log::info($event->sheet->getTitle());
        if ($event->sheet->getTitle() === 'Datas') {
            $spreadsheet = $event->sheet->getParent();
            // \Log::info($spreadsheet);
            $dataSheet = $spreadsheet->getSheetByName('Datas');
            // \Log::info($dataSheet);

            // Syntaxe alternative plus explicite
            $namedRange = new \PhpOffice\PhpSpreadsheet\NamedRange(
                'sites',
                $dataSheet,
                'A2:A50' // Sans les $
            );

            \Log::info($namedRange);

            $spreadsheet->addNamedRange($namedRange);

            $spreadsheet->addDefinedName(
                \PhpOffice\PhpSpreadsheet\NamedRange::createFromRange(
                    'buildings',
                    $dataSheet,
                    'B2:B50'
                )
            );

            // Vérification debug
            \Log::info('Named ranges:', $spreadsheet->getNamedRanges());
        }
    }

    // public function registerEvents(): array
    // {
    //     \Log::info('registerEvents');
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
                
    //         }
    //     ];
    // }
}
