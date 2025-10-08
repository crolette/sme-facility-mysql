<?php

namespace App\Imports;

use App\Imports\AssetsDataImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssetsImport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            'Assets' => new AssetsDataImport(),
        ];
    }
}
