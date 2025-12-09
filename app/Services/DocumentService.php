<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Company;
use App\Jobs\CompressPictureJob;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentService
{

    public function store(array $file)
    {
        if (TenantLimits::canAddFile($file['file']->getSize())) {
            $tenantId = tenancy()->tenant->id;

            $uuid = Str::substr(Str::uuid(), 0, 8);
            $directory = "$tenantId/documents/" . Carbon::now()->isoFormat('YYYYMMDD') . "/$uuid/";
            $fileName = Carbon::now()->isoFormat('YYYYMMDDhhmm') . '_' . Str::slug($file['name'], '-') . '_' . $uuid  . '.' . $file['file']->extension();

            $path = Storage::disk('tenants')->putFileAs($directory, $file['file'], $fileName);

            $document = new Document([
                'path' => $path,
                'filename' => $fileName,
                'directory' => $directory,
                'name' => $file['name'],
                'description' => $file['description'] ?? null,
                'size' => $file['file']->getSize(),
                'mime_type' => $file['file']->getMimeType(),
            ]);

            $document->documentCategory()->associate($file['typeId']);
            $document->uploader()->associate(Auth::guard('tenant')->user());
            $document->save();

            if (in_array($file['file']->extension(), ['png', 'jpg', 'jpeg'])) {
                CompressPictureJob::dispatch($document)->onQueue('default');
            }

            return $document;
        }
    }

    public function uploadAndAttachDocuments(Model $model, array $files): void
    {
        foreach ($files as $file) {
            $document = $this->store($file);
            if ($document) {

                $model->documents()->attach($document);

                if (in_array($file['file']->extension(), ['png', 'jpg', 'jpeg'])) {
                    CompressPictureJob::dispatch($document)->onQueue('default');
                }
            }
        }
    }

    public function detachDocumentFromModel(Model $model, int $documentId)
    {
        $document = Document::find($documentId);
        $model->documents()->detach($document);
    }

    public function attachExistingDocumentsToModel(Model $model, $request): void
    {
        foreach ($request as $documentId) {
            if (!$model->documents()->find($documentId)) {
                $document = Document::find($documentId);
                $model->documents()->attach($document);
            }
        }
    }

    public function verifyRelatedDocuments(Document $document)
    {
        if (count($document->getDocumentablesFlat()) === 0) {
            $this->deleteDocumentFromStorage($document);
        };
    }

    public function deleteDocumentFromStorage(Document $document)
    {
        Storage::disk('tenants')->delete($document->path);

        $this->deleteDirectoryFromStorage($document->directory);
        $document->delete();
    }

    public function deleteDirectoryFromStorage(string $directory)
    {
        if (count(Storage::disk('tenants')->files($directory)) === 0)
            Storage::disk('tenants')->deleteDirectory($directory);
    }
};
