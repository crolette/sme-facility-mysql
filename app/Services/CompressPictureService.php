<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Company;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CompressPictureService
{

    /**
     * compressPicture
     *
     * @param  mixed $picture
     * @return void
     */
    public function compressPicture(Model $picture)
    {
        Log::info('--- START COMPRESSING PICTURE : ' . $picture->filename);


        Company::decrementDiskSize($picture->size);

        $path = $picture->path;

        $newFileName = Str::chopEnd($picture->filename, ['.png', '.jpg', '.jpeg']) . '.webp';
        Log::info('newFileName : ' . $newFileName);

        $image = Image::read(Storage::disk('tenants')->path($path))->scaleDown(width: 1200, height: 1200)
            ->toWebp(quality: 75);

        $saved = Storage::disk('tenants')->put($picture->directory . '/' . $newFileName, $image);

        $picture->update([
            'path' => $picture->directory . '/' . $newFileName,
            'filename' => $newFileName,
            'size' => $image->size(),
            'mime_type' => $image->mimetype(),
        ]);

        Company::incrementDiskSize($image->size());

        if ($saved)
            Storage::disk('tenants')->delete($path);

        Log::info('--- END COMPRESSING PICTURE : ' . $picture->filename);
    }
};
