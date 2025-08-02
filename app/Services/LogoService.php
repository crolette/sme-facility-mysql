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
    protected $tenantId;
    protected $directory;

    public function __construct()
    {
        $this->tenantId = tenancy()->tenant->id ?? null;
        $this->directory = "$this->tenantId/providers/";
    }

    public function uploadAndAttachLogo(Provider $provider, $file, ?string $name = null): Provider
    {
        $directory = $this->directory . $provider->id . '/logo';
        if ($provider->logo !== null) {
            $provider = $this->deleteExistingFiles($provider);
        }

        $fileName = Carbon::now()->isoFormat('YYYYMMDDHHMM') . '_logo_' . Str::slug($name ?? $provider->name, '-') . '.' . $file->extension();
        $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

        $provider->logo = $path;

        return $provider;
    }

    public function deleteExistingFiles(Provider $provider)
    {
        $directory = $this->directory . $provider->id . '/logo';
        $files = Storage::disk('tenants')->files($directory);
        if (count($files) > 0) {
            foreach ($files as $file) {
                Storage::disk('tenants')->delete($file);
            }
        }

        $provider->logo = null;
        $provider->save();

        return $provider;
    }
};
