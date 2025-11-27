<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ProvidersExport implements WithMultipleSheets
{
    public function __construct(private array $providerIds = [], private $template = false) {}

    public function sheets(): array
    {
        return [
            'Providers' => new ProvidersSheet($this->providerIds, $this->template),
            'Datas' => new ProviderDataSheet()
        ];
    }
}
