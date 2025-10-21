<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentUpdateRequest;

class UpdateDocumentController extends Controller
{


    public function update(DocumentUpdateRequest $request, Document $document)
    {
        if (!$document)
            return ApiResponse::error('Document not found', [], 404);

        try {

            $document->update([...$request->validated()]);

            if ($document->category_type_id !== $request->validated('typeId')) {
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
