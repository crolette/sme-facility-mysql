<?php

namespace App\Http\Controllers\Tenants;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

class AssetTicketController extends Controller
{
    public function createFromAsset(Asset $asset)
    {
        return $this->create($asset);
    }

    public function createFromSite(Site $site)
    {
        return $this->create($site);
    }

    public function createFromBuilding(Building $building)
    {
        return $this->create($building);
    }

    public function createFromFloor(Floor $floor)
    {
        return $this->create($floor);
    }

    public function createFromRoom(Room $room)
    {
        return $this->create($room);
    }

    public function create(Model $model)
    {
        dd($model);
    }
}
