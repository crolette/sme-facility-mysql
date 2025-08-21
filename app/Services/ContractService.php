<?php

namespace App\Services;

use App\Models\Tenants\Contract;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    public function createWithModel(Model $model, $request): void
    {
        foreach ($request as $key => $contractRequest) {
            $contract = new Contract([...$contractRequest]);
            $contract->provider()->associate($contractRequest['provider_id']);
            $contract->save();
            $model->contracts()->attach($contract);
            $model->save();
        }
    }

    public function create($request): Contract | string
    {
        $contract = new Contract([...$request]);
        $contract->provider()->associate($request['provider_id']);
        $contract->save();

        foreach ($request['contractables'] as $contractable) {

            $modelMap = [
                'site' => \App\Models\Tenants\Site::class,
                'building' => \App\Models\Tenants\Building::class,
                'floor' => \App\Models\Tenants\Floor::class,
                'room' => \App\Models\Tenants\Room::class,
                'asset' => \App\Models\Tenants\Asset::class,
            ];

            $model = $modelMap[$contractable['locationType']]::where('code', $contractable['locationCode'])->first();

            $model->contracts()->attach($contract);
            $model->save();
        }



        return $contract;
    }

    public function update(Contract $contract, $request)
    {

        $contract->update([...$request]);

        if ($contract->provider->id !== $request['provider_id']) {
            $contract->provider()->disassociate();
            $contract->provider()->associate($request['provider_id']);
        }

        $contract->save();
    }
};
