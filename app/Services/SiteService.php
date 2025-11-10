<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Building;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SiteService
{

    public function __construct(protected DocumentService $documentService, protected PictureService $pictureService) {}

    // public function create(array $data): Site
    // {

    //     $site = new Site([
    //         ...$data,
    //     ]);

    //     return $site;
    // }

    // public function update(Site $site, array $data)
    // {
    //     $site->update(
    //         [
    //             ...$data
    //         ],
    //     );

    //     return $site;
    // }

    public function deleteSite(Site $site): bool
    {
        try {
            DB::beginTransaction();

            $documents = $site->documents;

            foreach ($documents as $document) {
                $this->documentService->detachDocumentFromModel($site, $document->id);
                $this->documentService->verifyRelatedDocuments($document);
            };

            $pictures = $site->pictures;
            foreach ($pictures as $picture) {
                $this->pictureService->deletePictureFromStorage($picture);
            };

            Storage::disk('tenants')->deleteDirectory($site->directory);

            $deleted = $site->delete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            Log::info('Error during site deletion', ['site' => $site, 'error' => $e->getMessage()]);
            DB::rollBack();
            return false;
        }

        return false;
    }
};
