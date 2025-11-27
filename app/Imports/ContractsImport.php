<?php

namespace App\Imports;

use App\Imports\ProvidersDataImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContractsImport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            'Contracts' => new ContractsDataImport(),
        ];
    }
}
