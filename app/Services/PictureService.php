<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PictureService
{
    public function uploadAndAttachPictures(Model $model, array $files, ?string $email = null): void
    {
        $tenantId = tenancy()->tenant->id;
        $modelType = Str::plural(Str::lower(class_basename($model))); // e.g., "assets", "sites", "buildings"
        $modelId = $model->id;

        foreach ($files as $file) {
            try {
                Debugbar::info('pictureservice', $file);
                $directory = "$tenantId/$modelType/$modelId/pictures";

                $newfileName = Str::chopEnd($file->getClientOriginalName(), ['.png', '.jpg', '.jpeg']);

                $fileName = Carbon::now()->isoFormat('YYYYMMDDHHMM') . '_' . Str::slug($newfileName, '-') . '_' . Str::substr(Str::uuid(), 0, 8) . '.' . $file->extension();

                // TODO Compress pictures
                $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

                $picture = new Picture([
                    'path' => $path,
                    'filename' => $fileName,
                    'directory' => $directory,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploader_email' => $email ?? null
                ]);

                if (Auth::guard('tenant')->check()) {
                    $picture->uploader()->associate(Auth::guard('tenant')->user());
                }

                $model->pictures()->save($picture);
            } catch (Exception $e) {
                dump('Erreur:', $e->getMessage());
            }
        }
    }
};
