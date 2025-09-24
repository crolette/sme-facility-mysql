<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use tbQuar\Facades\Quar;

class QRCodeService
{
    public function createAndAttachQR(Model $model): void
    {
        $tenantId = tenancy()->tenant->id;
        $modelType = Str::plural(Str::lower(class_basename($model))); // e.g., "assets", "sites", "buildings"
        $modelId = $model->id;
        
        $directory = "$tenantId/$modelType/$modelId/qrcode/";

        $qr_hash = generateQRCodeHash($model);

        $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
        
        $route = route('tenant.' . $modelType . '.tickets.create', $qr_hash);

        $files = Storage::disk('tenants')->files($directory);

        if (count($files) > 0) {
            $this->deleteExistingQR($files);
        }
        
        $qr = Quar::format('png')
            ->size(300)
            ->margin(2)
            ->gradient(34, 78, 143, 37, 39, 41,  'vertical')
            ->generate($route);

        Storage::disk('tenants')->put($directory . $fileName, $qr);

        $model->update([
            'qr_code' => $directory . $fileName,
            'qr_hash' => $qr_hash
        ]);
    }

    public function deleteExistingQR($files)
    {
        foreach ($files as $file) {
            Storage::disk('tenants')->delete($file);
        }
    }
};
