<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function generate(Request $request)
    {
        $content = 'https://example.com';
        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/assets/1/qrcode/";

        $qr = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate('https://example.com');

        $fileName = 'qr_' . time() . '.png';

        Storage::disk('tenants')->put($directory . $fileName, $qr);

        return response($qr)->header('Content-Type', 'image/png');
    }
}
