<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\RelocateRoomRequest;

class RelocateRoomController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected AssetService $assetService,

    ) {}

    public function relocateRoom(RelocateRoomRequest $request, Room $room)
    {
        Debugbar::info($request->validated());
        try {

            $roomType = LocationType::find($request->validated('locationType'));

            $count = Room::where('location_type_id', $roomType->id)->where('level_id', $room->level_id)->count();
            $codeNumber = generateCodeNumber($count + 1, $roomType->prefix, 3);

            $referenceCode = $room->floor->reference_code . '-' . $codeNumber;

            $room->update([
                'code' => $codeNumber,
                'reference_code' => $referenceCode
            ]);

            // relocation to new location Type
            $room->locationType()->dissociate();
            $room->locationType()->associate($roomType);

            $room->save();

            $this->qrCodeService->createAndAttachQR($room);

            $assets = $request->validated('assets');
            foreach ($assets as $asset) {
                $asset = $this->assetService->attachLocation(Asset::find($asset['assetId']), $asset['locationType'], $asset['locationId']);
                $asset->save();
            };

            return ApiResponse::success('', 'Success');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
        return ApiResponse::error();
    }
}
