<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Document;
use Barryvdh\Debugbar\Facades\Debugbar;

class UpdateDocumentController extends Controller
{

    public function update(Document $document) {}

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->noContent();
    }
}
