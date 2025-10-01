<?php

namespace App\Services;

use App\Jobs\CompressPictureJob;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PictureService
{
    public function uploadAndAttachPictures(Model $model, array $files, ?string $email = null): void
    {
        $tenantId = tenancy()->tenant->id;
        $modelType = Str::plural(Str::lower(class_basename($model))); // e.g., "assets", "sites", "buildings", "tickets",...
        $modelId = $model->id;

        foreach ($files as $file) {
            try {
                $directory = "$tenantId/$modelType/$modelId/pictures"; // e.g., "webxp/tickets/1/pictures"

                $newfileName = Str::chopEnd($file->getClientOriginalName(), ['.png', '.jpg', '.jpeg']);

                $fileName = Carbon::now()->isoFormat('YYYYMMDDHHMM') . '_' . Str::slug($newfileName, '-') . '_' . Str::substr(Str::uuid(), 0, 8) . '.' . $file->extension();


                $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

                $picture = new Picture([
                    'path' => $directory . '/' . $fileName,
                    'filename' => $fileName,
                    'directory' => $directory,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploader_email' => $email ?? Auth::guard('tenant')->user()->email
                ]);

                if (Auth::guard('tenant')->check()) {
                    $picture->uploader()->associate(Auth::guard('tenant')->user());
                }

                $model->pictures()->save($picture);

                Log::info('DISPATCH COMPRESS PICTURE JOB');
                CompressPictureJob::dispatch($picture)->onQueue('default');

            } catch (Exception $e) {
                Log::info('Erreur: ' . $e->getMessage());
            }
        }
    }

    public function compressPicture(Picture $picture) {
        Log::info('--- START COMPRESSING PICTURE : ' . $picture->filename);

        $path = $picture->path;
        
        $newFileName = Str::chopEnd($picture->filename, ['.png', '.jpg', '.jpeg']) . '.webp';
        Log::info('newFileName : ' . $newFileName);

        $image = Image::read(Storage::disk('tenants')->path($path))->scaleDown(width: 1200, height: 1200)
            ->toWebp(quality: 75);

        $saved = Storage::disk('tenants')->put($picture->directory . '/' . $newFileName , $image);

        $picture->update([
            'path' => $picture->directory . '/' . $newFileName,
            'filename' => $newFileName,
            'size' => $image->size(),
            'mime_type' => $image->mimetype(),
        ]);

        if($saved)
            Storage::disk('tenants')->delete($path);

        Log::info('--- END COMPRESSING PICTURE : ' . $picture->filename);

    }
};
