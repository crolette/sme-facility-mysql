<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\DocumentUpdateRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Services\DocumentService;

class DestroyDocumentController extends Controller
{

    public function __construct(protected DocumentService $documentService) {}

    public function destroy(Document $document)
    {
        if (!$document)
            return ApiResponse::error('Document not found', [], 404);

        try {

            $this->documentService->deleteDocumentFromStorage($document);

            return ApiResponse::success(null, 'Document deleted');
        } catch (Exception $e) {
            return ApiResponse::error('Impossible de mettre à jour le document.', [
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
