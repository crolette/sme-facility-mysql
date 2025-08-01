<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService
{
    public function createAndAttachQR(Model $model): void
    {
        $tenantId = tenancy()->tenant->id;
        $modelType = Str::plural(Str::lower(class_basename($model))); // e.g., "assets", "sites", "buildings"
        $modelId = $model->id;

        $directory = "$tenantId/$modelType/$modelId/qrcode/";
        if ($modelType === 'assets') {
            $fileName = 'qr_'  . $model->code . '_' . Carbon::now()->isoFormat('YYYYMMDD')  . '.png';
        } else {
            $fileName = 'qr_'  . $model->reference_code . '_' . Carbon::now()->isoFormat('YYYYMMDD')  . '.png';
        }

        $files = Storage::disk('tenants')->files($directory);

        if (count($files) > 0) {
            $this->deleteExistingQR($files);
        }

        if ($modelType === 'assets') {
            $route = route('tenant.assets.tickets.create', $model->code);
        } else {
            $route = route('tenant.' . $modelType . '.tickets.create', $model->reference_code);
        }

        $qr = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($route);

        Storage::disk('tenants')->put($directory . $fileName, $qr);

        $model->update(['qr_code' => $directory . $fileName]);
    }

    public function deleteExistingQR($files)
    {
        foreach ($files as $file) {
            Storage::disk('tenants')->delete($file);
        }
    }
};
