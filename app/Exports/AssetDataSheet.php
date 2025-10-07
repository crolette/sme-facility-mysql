<?php

namespace App\Exports;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetDataSheet implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $sites = Site::select('id','reference_code', 'location_type_id')->get();
        $sitesConcatenated = $sites->map(function ($site) {
            return $site->reference_code . ' - ' . $site->name;
        });

        $buildings = Building::select('id', 'reference_code', 'location_type_id')->get();
        $buildingsConcatenated = $buildings->map(function ($building) {
            return $building->reference_code . ' - ' . $building->name;
        });

        $floors = Floor::select('id', 'reference_code', 'location_type_id')->get();
        $floorsConcatenated = $floors->map(function ($floor) {
            return $floor->reference_code . ' - ' . $floor->name;
        });
        $rooms = Room::select('id', 'reference_code', 'location_type_id')->get();
        $roomsConcatenated = $rooms->map(function ($room) {
            return $room->reference_code . ' - ' . $room->name;
        });

        // Trouver la longueur max
        $maxRows = max($sites->count(), $buildings->count(), $floors->count(), $rooms->count());

        $data = collect();
        for ($i = 0; $i < $maxRows; $i++) {
            $data->push([
                $sitesConcatenated->get($i, ''),
                $buildingsConcatenated->get($i, ''),
                $floorsConcatenated->get($i, ''),
                $roomsConcatenated->get($i, ''),
            ]);
        }

        return $data;
    }

    public function headings(): array 
    {
        return [
            'sites', 
            'buildings',
            'floors',
            'rooms'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $spreadsheet = $event->sheet->getParent();

                $spreadsheet->addNamedRange(
                    new \PhpOffice\PhpSpreadsheet\NamedRange(
                        'sites',
                        $event->sheet->getDelegate(),
                        '$A$2:$A$50'
                    )
                );

                $spreadsheet->addNamedRange(
                    new \PhpOffice\PhpSpreadsheet\NamedRange(
                        'buildings',
                        $event->sheet->getDelegate(),
                        '$B$2:$B$50'
                    )
                );
            }
        ];
    }
}
