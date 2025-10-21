<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Services\DocumentService;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\SingleDocumentUploadRequest;

class UploadDocumentController extends Controller
{
    public function __construct(protected DocumentService $documentService) {}

    public function store(SingleDocumentUploadRequest $request)
    {
        // TODO Document Policy
        try {
            $document = $this->documentService->store($request->validated());
            return ApiResponse::success(null, 'Document created');
        } catch (Exception $e) {
            return ApiResponse::error('Error while uploading document.', [
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
