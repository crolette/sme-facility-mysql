<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use Illuminate\Support\Facades\DB;
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
            DB::beginTransaction();
            if ($request->validated('locationType') !== $room->locationType->id) {

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
            }

            $assets = $request->validated('assets');
            foreach ($assets as $asset) {
                if ($asset['change'] == 'delete') {
                    $response = $this->assetService->deleteAsset(Asset::find($asset['assetId']));
                    if (!$response) {
                        throw new Exception("Error deleting asset");
                    }
                } else {
                    $asset = $this->assetService->attachLocation(Asset::find($asset['assetId']), $asset['locationType'], $asset['locationId']);
                    if (!$asset) {
                        throw new Exception("Error Processing Request");
                    }
                    $asset->save();
                }
            };
            DB::commit();
            return ApiResponse::success(['reference_code' => $room->reference_code], 'Success');
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage());
        }
        return ApiResponse::error();
    }
}
