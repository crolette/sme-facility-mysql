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
        return ApiResponse::success($asset->meterReadings);
    }

    public function store(Request $request, Asset $asset)
    {

        $validated = Validator::make(
            $request->all(),
            [
                'meter' => 'required|decimal:0,2|gt:0',
                'meter_date' => 'required|date',
                'notes' => 'nullable|string|'
            ]
        );

        if ($validated->validated()['meter'] < $asset->meterReadings()->where('meter_date', '<', $validated->validated()['meter_date'])->orderBy('meter_date', 'desc')->first()?->meter)
            return ApiResponse::error('Meter should be higher than the last one');

        try {
            $meterReading = new MeterReading([...$validated->validated()]);
            $meterReading->user()->associate($request->user());
            $meterReading->asset()->associate($asset);
            $meterReading->save();

            return ApiResponse::success('', 'Meter Reading added ');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return ApiResponse::error($e->getMessage());
        }
    }

    public function update(Request $request, MeterReading $meterReading)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'meter' => 'required|decimal:0,2|gt:0',
                'meter_date' => 'required|date',
                'notes' => 'nullable|string|'
            ]
        );

        if ($validated->validated()['meter'] < MeterReading::where('asset_id', $meterReading->asset_id)->where('meter_date', '<', $validated->validated()['meter_date'])->orderBy('meter_date', 'desc')->first()?->meter)
            return ApiResponse::error('Meter should be higher than the last one');


        try {
            $meterReading->update([...$validated->validated()]);

            return ApiResponse::success('', 'Meter Reading updated');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return ApiResponse::error($e->getMessage());
        }
    }

    public function destroy(MeterReading $meterReading)
    {
        try {
            $meterReading->delete();

            return ApiResponse::success('', 'Meter Reading deleted');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return ApiResponse::error($e->getMessage());
        }
    }
}
