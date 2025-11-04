<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ProvidersExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Providers' => new ProvidersSheet(),
            'Datas' => new ProviderDataSheet()
        ];
    }
}
