<?php

namespace App\Exports;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class AssetDataSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    use RegistersEventListeners;

    public function title(): string
    {
        return 'Datas';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $sites = Site::select('id', 'reference_code', 'location_type_id')->get();
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

        $users = User::select('id', 'first_name', 'last_name', 'email')->whereNull('provider_id')->get();
        $usersConcatenated = $users->map(function ($user) {
            return $user->full_name . ' - ' . $user->email;
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
                $usersConcatenated->get($i, ''),
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

    public static function afterSheet(AfterSheet $event)
    {
        // if ($event->sheet->getTitle() === 'Datas') {
        $spreadsheet = $event->sheet->getParent();
        // \Log::info($spreadsheet);
        $dataSheet = $spreadsheet->getSheetByName('Datas');
        // \Log::info($dataSheet);

        // Syntaxe alternative plus explicite
        $sites = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'sites',
            $dataSheet,
            '$A$2:$A$50'
        );

        $spreadsheet->addNamedRange($sites);

        $buildings = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'buildings',
            $dataSheet,
            '$B$2:$B$50'
        );

        $spreadsheet->addNamedRange($buildings);

        $floors = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'floors',
            $dataSheet,
            '$C$2:$C$50'
        );

        $spreadsheet->addNamedRange($floors);

        $rooms = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'rooms',
            $dataSheet,
            '$D$2:$D$50'
        );

        $spreadsheet->addNamedRange($rooms);

        $users = new \PhpOffice\PhpSpreadsheet\NamedRange(
            'users',
            $dataSheet,
            '$E$2:$E$500'
        );

        $spreadsheet->addNamedRange($users);

        // }
    }
}
