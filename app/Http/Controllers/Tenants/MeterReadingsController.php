<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Tenants\MeterReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MeterReadingsController extends Controller
{
    public function index(Asset $asset)
    {
        return ApiResponse::success($asset->meter_readings);
    }

    public function store(Request $request, Asset $asset)
    {

        $validated = Validator::make(
            $request->all(),
            [
                'meter' => 'required|decimal:2|gt:0',
                'meter_date' => 'required|date',
                'notes' => 'nullable|string|'
            ]
        );

        try {
            $meterReading = new MeterReading([...$validated->validated()]);
            $meterReading->user()->associate($request->user());
            $meterReading->asset()->associate($asset);
            $meterReading->save();

            return ApiResponse::success('', 'Meter Reading added');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return ApiResponse::error($e->getMessage());
        }
    }
}
