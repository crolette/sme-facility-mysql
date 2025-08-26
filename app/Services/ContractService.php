<?php

namespace App\Services;

use App\Enums\ContractDurationEnum;
use App\Enums\NoticePeriodEnum;
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

            if (isset($contractRequest['contract_duration']))
                $contract = $this->updateContractEndDate($contract, $contractRequest['contract_duration']);

            $contract->notice_period = isset($contractRequest['notice_period']) ? $contractRequest['notice_period'] : 'default';

            $contract = $this->updateNoticeDate($contract, $contract->notice_period);

            $contract->provider()->associate($contractRequest['provider_id']);
            $contract->save();
            $model->contracts()->attach($contract);
            $model->save();
        }
    }

    public function create($request): Contract | string
    {
        $contract = new Contract([...$request]);

        if (isset($request['contract_duration']))
            $contract = $this->updateContractEndDate($contract, $request['contract_duration']);

        $contract->notice_period = isset($contractRequest['notice_period']) ? $contractRequest['notice_period'] : 'default';

        $contract = $this->updateNoticeDate($contract, $contract->notice_period);

        $contract->provider()->associate($request['provider_id']);
        $contract->save();

        if (isset($request['contractables']))
            $contract = $this->syncContractables($contract, $request['contractables']);

        return $contract;
    }

    public function update(Contract $contract, $request)
    {

        $contract->update([...$request]);

        if (isset($request['contract_duration']) && ($contract->wasChanged('contract_duration') || $contract->wasChanged('start_date')))
            $contract = $this->updateContractEndDate($contract, $request['contract_duration']);

        if ($contract->wasChanged('notice_period') || $contract->wasChanged('contract_duration') || $contract->wasChanged('start_date'))
            $contract = $this->updateNoticeDate($contract, isset($request['notice_period']) ? $request['notice_period'] : 'default');

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

    public function updateContractEndDate(Contract $contract, $contract_duration): Contract
    {
        $contractDuration = ContractDurationEnum::tryFrom($contract_duration);

        if ($contract->start_date)
            $endDate = $contractDuration->addTo(Carbon::createFromFormat('Y-m-d', $contract->start_date));
        else {
            $contract->start_date = Carbon::now();
            $endDate = $contractDuration->addTo(Carbon::now());
        }

        $contract->end_date = $endDate;

        return $contract;
    }

    public function updateNoticeDate(Contract $contract, $notice_period): Contract
    {

        $contract->notice_date = $notice_period->subFrom(Carbon::parse($contract->end_date));
        return $contract;
    }
};
