<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\DocumentUpdateRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class DestroyDocumentController extends Controller
{

    public function destroy(Document $document)
    {
        if (!$document)
            return ApiResponse::error('Document not found', [], 404);

        try {

            Storage::disk('tenants')->delete($document->path);
            if (count(Storage::disk('tenants')->files($document->directory)) === 0)
                Storage::disk('tenants')->deleteDirectory($document->directory);

            $document->delete();

            return ApiResponse::success(null, 'Document deleted');
        } catch (Exception $e) {
            return ApiResponse::error('Impossible de mettre à jour le document.', [
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
