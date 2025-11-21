<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\LocationType;
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

    public function create(array $data): Site
    {
        $locationType = LocationType::find($data['locationType']);
        $count = Site::where('location_type_id', $locationType->id)->count();

        $codeNumber = generateCodeNumber($count + 1, $locationType->prefix);

        $site = Site::create([
            ...$data,
            'code' => $codeNumber,
            'floor_material_id'  =>
            isset($data['floor_material_id']) && $data['floor_material_id'] === 'other'
                ? null
                : (isset($data['floor_material_id']) ? $data['floor_material_id'] : null),
            'wall_material_id'  => isset($data['wall_material_id']) && $data['wall_material_id'] === 'other'
                ? null
                : (isset($data['wall_material_id']) ? $data['wall_material_id'] : null),
            'reference_code' => $codeNumber,
            'location_type_id' => $locationType->id,
        ]);

        return $site;
    }

    public function update(Site $site, array $data): Site
    {

        $site->update([
            ...$data,
            'floor_material_id'  =>
            isset($data['floor_material_id']) && $data['floor_material_id'] === 'other'
                ? null
                : (isset($data['floor_material_id']) ? $data['floor_material_id'] : null),
            'wall_material_id'  => isset($data['wall_material_id']) && $data['wall_material_id'] === 'other'
                ? null
                : (isset($data['wall_material_id']) ? $data['wall_material_id'] : null),
        ]);

        return $site;
    }



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
