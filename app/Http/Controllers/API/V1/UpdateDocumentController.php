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
use App\Http\Requests\Tenant\DocumentUpdateRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class UpdateDocumentController extends Controller
{


    public function update(DocumentUpdateRequest $request, Document $document)
    {
        if (!$document)
            return ApiResponse::error('Document not found', [], 404);

        try {

            $document->update([...$request->validated()]);

            if ($document->category_type_id !== $request->validated('typeId')) {
                $document->documentCategory()->dissociate();
                $document->documentCategory()->associate($request->validated('typeId'));
                $document->save();
            }

            return ApiResponse::success(null, 'Document updated');
        } catch (Exception $e) {
            return ApiResponse::error('Impossible de mettre à jour le document.', [
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->noContent();
    }
}
