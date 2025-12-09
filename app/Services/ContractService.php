<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Enums\ContractDurationEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    public function __construct(protected DocumentService $documentService) {}

    public function createWithModel(Model $model, $request): void
    {

        foreach ($request as $key => $contractRequest) {
            $contract = new Contract([...$contractRequest]);

            if (isset($contractRequest['provider_id'])) {
                $contract->provider()->associate($contractRequest['provider_id'])->save();
            }

            if (isset($contractRequest['files']))
                app(DocumentService::class)->uploadAndAttachDocuments($contract, $contractRequest['files']);

            $model->contracts()->attach($contract);
            $model->save();
        }
    }

    public function sendExpiredContractMailToUsers(Contract $contract)
    {
        $users = User::role('Admin')->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(
                new \App\Mail\ContractExpiredMail($contract)
            );
            Log::info("Mail sent to : {$user->email}");
        }

        // Create notifications for related assets/locations with manager
        $contract = Contract::with(['assets', 'sites', 'rooms', 'floors', 'buildings'])->find($contract->id);
        $contractables = $contract->contractables();
        // dump(count($contractables));
        $contractables->each(function ($contractable) use ($contract) {
            // dump('contractables');
            if ($contractable->manager) {
                Mail::to($contractable->manager->email)->send(
                    new \App\Mail\ContractExpiredMail($contract)
                );
                Log::info("Mail sent to : {$contractable->manager->email}");
            }
        });
    }

    public function sendExtendedContractMailToUsers(Contract $contract)
    {
        $users = User::role('Admin')->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(
                new \App\Mail\ContractExpiredMail($contract)
            );
            Log::info("Mail sent to : {$user->email}");
        }

        // Create notifications for related assets/locations with manager
        $contract = Contract::with(['assets', 'sites', 'rooms', 'floors', 'buildings'])->find($contract->id);
        $contractables = $contract->contractables();
        // dump(count($contractables));
        $contractables->each(function ($contractable) use ($contract) {
            // dump('contractables');
            if ($contractable->manager) {
                Mail::to($contractable->manager->email)->send(
                    new \App\Mail\ContractExpiredMail($contract)
                );
                Log::info("Mail sent to : {$contractable->manager->email}");
            }
        });
    }

    public function associateProviderToContractWhenImport(Contract $contract, $data): Contract
    {
        $provider = Provider::where('name', $data)->first();
        $contract->provider()->associate($provider)->save();

        return $contract;
    }

    public function attachExistingContractsToModel(Model $model, $request): void
    {
        foreach ($request as $contractId) {
            $contract = Contract::find($contractId);
            if (!$model->contracts()->find($contractId))
                $model->contracts()->attach($contract);
        }
    }

    public function detachExistingContractFromModel(Model $model, $contractId)
    {
        $contract = Contract::find($contractId);

        $model->contracts()->detach($contract);
    }

    public function create($request): Contract | string
    {
        $contract = new Contract([...$request]);

        if (isset($contractRequest['provider_id'])) {
            $contract->provider()->associate($request['provider_id'])->save();
        }

        $contract->save();

        if (isset($request['contractables']))
            $contract = $this->syncContractables($contract, $request['contractables']);

        if (isset($request['files']))
            app(DocumentService::class)->uploadAndAttachDocuments($contract, $request['files']);

        return $contract;
    }

    public function update(Contract $contract, $request)
    {
        // dump('update contract service');
        $contract->update([...$request]);

        if (($contract->wasChanged('notice_period') && !isset($request['notice_period'])))
            $contract->notice_date = null;

        if (isset($request['provider_id'])) {
            if ($contract->provider->id !== $request['provider_id']) {
                $contract->provider()->associate($request['provider_id']);
            }
        }

        $contract->save();

        if (isset($request['contractables']))
            $contract = $this->syncContractables($contract, $request['contractables']);


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

    public function updateContractEndDate(Contract $contract, ContractDurationEnum $contract_duration): Contract
    {
        // dump('updateContractEndDate');

        if ($contract->start_date) {
            $endDate = $contract_duration->addTo($contract->start_date);
        } else {
            $contract->start_date = Carbon::now();
            $endDate = $contract_duration->addTo(Carbon::now());
        }

        $contract->end_date = $endDate;

        return $contract;
    }

    public function extendAutomaticContract(Contract $contract): Contract
    {
        $contract->start_date = Carbon::now();
        $contract->end_date = $contract->contract_duration->addTo(Carbon::now());

        if ($contract->notice_period)
            $contract->notice_date = $contract->notice_period->subFrom(Carbon::parse($contract->end_date));

        $contract->save();

        return $contract;
    }

    public function updateNoticeDate(Contract $contract, NoticePeriodEnum $notice_period): Contract
    {
        // TODO check if the notice date is > then start_date

        $contract->notice_date = $notice_period->subFrom(Carbon::parse($contract->end_date));

        return $contract;
    }

    public function delete(Contract $contract): bool
    {

        try {
            DB::beginTransaction();
            $deleted = $contract->delete();

            $documents = $contract->documents;
            foreach ($documents as $document) {
                $this->documentService->detachDocumentFromModel($contract, $document->id);
                $this->documentService->verifyRelatedDocuments($document);
            };

            Storage::disk('tenants')->deleteDirectory($contract->directory);

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            Log::info('Error during building deletion', ['site' => $contract, 'error' => $e->getMessage()]);
            DB::rollBack();
            return false;
        }

        return false;
    }
};
