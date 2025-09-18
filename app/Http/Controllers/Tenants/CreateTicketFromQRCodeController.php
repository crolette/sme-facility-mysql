<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;

class CreateTicketFromQRCodeController extends Controller
{
    public function createFromAsset(Asset $asset)
    {
        
        $asset = Asset::select('id', 'code', 'reference_code', 'location_type', 'location_id', 'category_type_id')->where('reference_code', $asset->reference_code)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($asset, 'assets');
    }

    public function createFromSite(Site $site)
    {
        $site = Site::select('id', 'code', 'reference_code', 'location_type_id')->where('reference_code', $site->reference_code)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($site, 'sites');
    }

    public function createFromBuilding(Building $building)
    {
        $building = Building::select('id', 'code', 'reference_code', 'location_type_id')->where('reference_code', $building->reference_code)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($building, 'buildings');
    }

    public function createFromFloor(Floor $floor)
    {
        $floor = Floor::select('id', 'code', 'reference_code', 'location_type_id')->where('reference_code', $floor->reference_code)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($floor, 'floors');
    }

    public function createFromRoom(Room $room)
    {
        $room = Room::select('id', 'code', 'reference_code', 'location_type_id')->where('reference_code', $room->reference_code)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($room, 'rooms');
    }

    public function create(Model $model, string $locationType)
    {
        return Inertia::render('tenants/tickets/CreateTicketFromQRCode', ['item' => $model, 'location_type' => $locationType]);
    }
}
