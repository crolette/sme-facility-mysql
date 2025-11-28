<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContractsExport implements WithMultipleSheets
{
    public function __construct(private array $contractIds = [], private $template = false) {}

    public function sheets(): array
    {
        return [
            'Contracts' => new ContractsSheet($this->contractIds, $this->template),
            'Datas' => new ContractsDataSheet()
        ];
    }
}
