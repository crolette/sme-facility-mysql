<?php

namespace App\Imports;

use App\Imports\ProvidersDataImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProvidersImport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            'Providers' => new ProvidersDataImport(),
        ];
    }
}
