<?php

namespace App\Services;

use App\Jobs\CompressCompanyLogoJob;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Company;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CompanyLogoService
{
    protected $tenantId;
    protected $directory;

    public function __construct()
    {
        $this->tenantId = tenancy()->tenant->id ?? null;
        $this->directory = "$this->tenantId/company/logo";
    }

    public function uploadAndAttachLogo(Company $company, $file, ?string $name = null)
    {

        $file = $file[0];

        if ($company->logo !== null) {
            $this->deleteExistingFiles($company);
        }

        $fileName = 'logo' . '.' . $file->extension();

        $path = Storage::disk('tenants')->putFileAs($this->directory, $file, $fileName);

        $company->logo = $path;
        $company->save();
        Company::incrementDiskSize($file->getSize());

        Log::info('DISPATCH COMPRESS COMPANY LOGO JOB');
        CompressCompanyLogoJob::dispatch($company)->onQueue('default');
    }

    public function compressLogo(Company $company)
    {
        Log::info('--- START COMPRESSING COMPANY LOGO : ' . $company->logo);

        Company::decrementDiskSize(Storage::disk('tenants')->size($company->logo));

        $path = $company->logo;

        $newFileName =  Str::chopEnd(basename(Storage::disk('tenants')->path($company->logo)), ['.png', '.jpg', '.jpeg']) . '.webp';

        $image = Image::read(Storage::disk('tenants')->path($company->logo))->scaleDown(width: 1200, height: 1200)
            ->toWebp(quality: 75);

        $saved = Storage::disk('tenants')->put($this->directory . '/' . $newFileName, $image);

        $company->update(
            [
                'logo' => $this->directory . '/' . $newFileName
            ]
        );

        if ($saved) {
            Storage::disk('tenants')->delete($path);
        }


        Company::incrementDiskSize(Storage::disk('tenants')->size($company->logo));

        Log::info('--- END COMPRESSING COMPANY LOGO : ' . $company->logo);
    }

    public function deleteExistingFiles(Company $company)
    {
        $files = Storage::disk('tenants')->files($this->directory);
        if (count($files) > 0) {
            foreach ($files as $file) {
                Company::decrementDiskSize(Storage::disk('tenants')->size($file));
                Storage::disk('tenants')->delete($file);
            }
        }

        Storage::disk('tenants')->deleteDirectory($this->directory);

        $company->logo = null;
        $company->save();

        return $company;
    }
};
