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
    public function createFromAsset(string $qr_hash)
    {
        $asset = Asset::select('id', 'code', 'reference_code', 'location_type', 'location_id', 'category_type_id')->where('qr_hash', $qr_hash)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($asset, 'assets');
    }

    public function createFromSite(string $qr_hash)
    {
        $site = Site::select('id', 'code', 'reference_code', 'location_type_id')->where('qr_hash', $qr_hash)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($site, 'sites');
    }

    public function createFromBuilding(string $qr_hash)
    {
        $building = Building::select('id', 'code', 'reference_code', 'location_type_id')->where('qr_hash', $qr_hash)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($building, 'buildings');
    }

    public function createFromFloor(string $qr_hash)
    {
        $floor = Floor::select('id', 'code', 'reference_code', 'location_type_id')->where('qr_hash', $qr_hash)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($floor, 'floors');
    }

    public function createFromRoom(string $qr_hash)
    {
        $room = Room::select('id', 'code', 'reference_code', 'location_type_id')->where('qr_hash', $qr_hash)->with('maintainable:id,maintainable_type,maintainable_id,name,description')->first();
        return $this->create($room, 'rooms');
    }

    public function create(Model $model, string $locationType)
    {
        $existingTickets = $model->tickets()->whereNull('closed_at')->select('description')->get();

        return Inertia::render('tenants/tickets/CreateTicketFromQRCode', ['item' => $model, 'location_type' => $locationType, 'existingTickets' => $existingTickets]);
    }
}
