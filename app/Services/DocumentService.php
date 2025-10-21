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

        Company::incrementDiskSize($file['file']->getSize());

        $document->documentCategory()->associate($file['typeId']);
        $document->uploader()->associate(Auth::guard('tenant')->user());
        $document->save();

        if (in_array($file['file']->extension(), ['png', 'jpg', 'jpeg'])) {
            CompressPictureJob::dispatch($document)->onQueue('default');
        }

        return $document;
    }

    public function uploadAndAttachDocuments(Model $model, array $files): void
    {
        foreach ($files as $file) {
            $document = $this->store($file);

            $model->documents()->attach($document);

            if (in_array($file['file']->extension(), ['png', 'jpg', 'jpeg'])) {
                CompressPictureJob::dispatch($document)->onQueue('default');
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
};
