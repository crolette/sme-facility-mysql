<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Company;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;
use App\Jobs\CompressProviderLogoJob;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
        $file = $file[0];

        $directory = $this->directory . $provider->id . '/logo';
        if ($provider->logo !== null) {
            $provider = $this->deleteExistingFiles($provider);
        }

        $fileName = Carbon::now()->isoFormat('YYYYMMDDhhmm') . '_logo_' . Str::slug($name ?? $provider->name, '-') . '.' . $file->extension();
        $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

        Company::incrementDiskSize($file->getSize());

        $provider->logo = $path;
        $provider->save();

        Log::info('DISPATCH COMPRESS LOGO JOB');
        CompressProviderLogoJob::dispatch($provider)->onQueue('default');

        return $provider;
    }

    public function compressLogo(Provider $provider)
    {
        Log::info('--- START COMPRESSING PROVIDER LOGO : ' . $provider->logo);

        $path = $provider->logo;

        Company::decrementDiskSize(Storage::disk('tenants')->size($provider->logo));

        $newFileName =  Str::chopEnd(basename(Storage::disk('tenants')->path($provider->logo)), ['.png', '.jpg', '.jpeg']) . '.webp';

        $image = Image::read(Storage::disk('tenants')->path($provider->logo))->scaleDown(width: 1200, height: 1200)
            ->toWebp(quality: 75);

        $saved = Storage::disk('tenants')->put($this->directory . $provider->id . '/logo/' . $newFileName, $image);

        $provider->update(
            [
                'logo' => $this->directory . $provider->id . '/logo/' . $newFileName
            ]
        );

        if ($saved)
            Storage::disk('tenants')->delete($path);


        Company::incrementDiskSize(Storage::disk('tenants')->size($provider->logo));

        Log::info('--- END COMPRESSING PROVIDER LOGO : ' . $provider->logo);
    }

    public function deleteExistingFiles(Provider $provider)
    {
        $directory = $this->directory . $provider->id . '/logo';
        $files = Storage::disk('tenants')->files($directory);
        if (count($files) > 0) {
            foreach ($files as $file) {
                Company::decrementDiskSize(Storage::disk('tenants')->size($file));
                Storage::disk('tenants')->delete($file);
            }
        }

        $provider->logo = null;
        $provider->save();

        return $provider;
    }
};
