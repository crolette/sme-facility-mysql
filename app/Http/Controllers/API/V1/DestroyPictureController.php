<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\DocumentUpdateRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class DestroyPictureController extends Controller
{

    public function destroy(Picture $picture)
    {
        if (!$picture)
            return ApiResponse::error('Picture not found', [], 404);

        try {

            Storage::disk('tenants')->delete($picture->path);
            if (count(Storage::disk('tenants')->files($picture->directory)) === 0)
                Storage::disk('tenants')->deleteDirectory($picture->directory);

            $picture->delete();


            return ApiResponse::success(null, 'Picture deleted');
        } catch (Exception $e) {
            return ApiResponse::error('Error deleting picture.', [
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
