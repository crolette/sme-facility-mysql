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

        if (isset($request['contractables']))
            $contract = $this->syncContractables($contract, $request['contractables']);


        return $contract;
    }

    public function update(Contract $contract, $request)
    {

        $contract->update([...$request]);

        if ($contract->provider->id !== $request['provider_id']) {
            $contract->provider()->disassociate();
            $contract->provider()->associate($request['provider_id']);
        }

        if (isset($request['contractables']))
            $contract = $this->syncContractables($contract, $request['contractables']);

        $contract->save();

        return $contract;
    }

    protected function syncContractables(Contract $contract, array $contractables)
    {
        $groupedAssetLocations = collect($contractables)->groupBy('locationType')->map(function ($items) {
            return $items->pluck('locationId')->toArray();
        });

        $morphs = ['asset', 'site', 'building', 'floor', 'room'];

        foreach ($morphs as $morph) {
            $contract->{$morph . 's'}()->sync($groupedAssetLocations[$morph] ?? []);
        }

        return $contract;
    }
};
