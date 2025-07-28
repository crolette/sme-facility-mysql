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

class QRService
{
    public function createAndAttachQR(Model $model): void
    {
        $tenantId = tenancy()->tenant->id;
        $modelType = Str::plural(Str::lower(class_basename($model))); // e.g., "assets", "sites", "buildings"
        $modelId = $model->id;

        $directory = "$tenantId/$modelType/$modelId/qrcode/";
        $fileName = 'qr_'  . $model->code . '_' . Carbon::now()->isoFormat('YYYYMMDD')  . '.png';

        $qr = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate(route('tenant.assets.tickets.create', $model->code));

        Storage::disk('tenants')->put($directory . $fileName, $qr);

        $model->update(['qr_code' => $directory . $fileName]);


        // $document->documentCategory()->associate($file['typeId']);
        // $document->uploader()->associate(Auth::guard('tenant')->user());
        // $document->save();

        // Attach to model (ensure polymorphic or many-to-many is set up accordingly)
        // $model->documents()->attach($document);
    }
};
