<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LogoService
{
    public function uploadAndAttachLogo(Provider $provider, $file, string $name): Provider
    {

        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/company/";

        $files = Storage::disk('tenants')->files($directory);

        if (count($files) > 0) {
            $this->deleteExistingQR($files);
        }


        $fileName = Carbon::now()->isoFormat('YYYYMMDD') . '_logo_' . Str::slug($name, '-') . '.' . $file->extension();
        $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

        $provider->logo = $path;

        return $provider;
    }

    public function deleteExistingFiles($files)
    {
        foreach ($files as $file) {
            Storage::disk('tenants')->delete($file);
        }
    }
};
