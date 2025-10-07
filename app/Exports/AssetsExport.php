<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssetsExport implements WithMultipleSheets
{
    private $fileName = 'assets.xlsx';
    

    public function sheets() : array
    {
        return [
            new AssetsSheet(),
            new AssetDataSheet()
        ];
    }
}
