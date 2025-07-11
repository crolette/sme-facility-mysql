<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function uploadAndAttachDocuments(Model $model, array $files): void
    {
        $tenantId = tenancy()->tenant->id;
        $modelType = Str::plural(Str::lower(class_basename($model))); // e.g., "assets", "sites", "buildings"
        $modelId = $model->id;

        foreach ($files as $file) {
            $directory = "$tenantId/$modelType/$modelId/documents";
            $fileName = Carbon::now()->isoFormat('YYYYMMDD') . '_' . Str::slug($file['name'], '-') . '_' . Str::substr(Str::uuid(), 0, 8) . '.' . $file['file']->extension();

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

            // Attach to model (ensure polymorphic or many-to-many is set up accordingly)
            $model->documents()->attach($document);
        }
    }
};
