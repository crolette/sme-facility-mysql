<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Company;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

        if ($company->logo !== null) {
            $this->deleteExistingFiles($company);
        }

        $fileName = 'logo'. '.' . $file->extension();
        $path = Storage::disk('tenants')->putFileAs($this->directory, $file, $fileName);

        $company->logo = $path;
        $company->save();

    }

    public function deleteExistingFiles(Company $company)
    {
        $files = Storage::disk('tenants')->files($this->directory);
        if (count($files) > 0) {
            foreach ($files as $file) {
                Storage::disk('tenants')->delete($file);
            }
        }

        $company->logo = null;
        $company->save();

        return $company;
    }
};
