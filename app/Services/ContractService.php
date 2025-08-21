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

    public function create($request) {}

    public function update(Contract $contract, $request)
    {

        $contract->update([...$request]);

        if ($contract->provider->id !== $request['provider_id']) {
            $contract->provider()->deassociate();
            $contract->provider()->associate($request['provider_id']);
        }

        $contract->save();
    }
};
