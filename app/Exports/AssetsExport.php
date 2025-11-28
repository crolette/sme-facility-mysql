<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssetsExport implements WithMultipleSheets
{
    public function __construct(private array $assetIds = [], private $template = false) {}

    public function sheets(): array
    {
        return [
            'Assets' => new AssetsSheet($this->assetIds, $this->template),
            'Datas' => new AssetDataSheet()
        ];
    }
}
