<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Enums\ContractDurationEnum;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\Log;
use App\Models\Tenants\Contractable;
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

            // $model->contracts()->attach($contract);
            $model->save();
            $this->attachContractToModel($contract, $model);
        }
    }

    public function create($request): Contract | string
    {
        $contract = new Contract([...$request]);

        $contract->save();

        if (isset($request['provider_id'])) {
            $contract->provider()->associate($request['provider_id'])->save();
        }

        if (isset($request['contractables']))
            $this->syncContractables($contract, $request['contractables']);

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
            $this->syncContractables($contract, $request['contractables']);

        return $contract;
    }

    public function attachContractToModel(Contract $contract, Model $model): void
    {
        Contractable::create([
            'contract_id' => $contract->id,
            'contractable_id' => $model->id,
            'contractable_type' => $model->getMorphClass(),
        ]);
    }

    public function detachContractFromModel(Contract $contract, Model $model): void
    {
        $toDelete = Contractable::where([
            'contract_id' => $contract->id,
            'contractable_id' => $model->id,
            'contractable_type' => $model->getMorphClass(),
        ])->first();

        $toDelete->delete();
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
            if (!$model->contracts()->find($contractId)) {
                // $model->contracts()->attach($contract);
                $this->attachContractToModel($contract, $model);
            }
        }
    }

    public function detachExistingContractFromModel(Model $model, $contractId)
    {
        $contract = Contract::find($contractId);

        $this->detachContractFromModel($contract, $model);
        // $model->contracts()->detach($contract);
    }

    // protected function syncContractables(Contract $contract, array $contractables)
    // {
    //     Debugbar::info($contract);
    //     Debugbar::info($contractables);
    //     foreach ($contractables as $contractable) {

    //         $location = match ($contractable['locationType']) {
    //             'asset'  => Asset::findOrFail($contractable['locationId']),
    //             'site'  => Site::findOrFail($contractable['locationId']),
    //             'building' => Building::findOrFail($contractable['locationId']),
    //             'floor' => Floor::findOrFail($contractable['locationId']),
    //             'room' => Room::findOrFail($contractable['locationId']),
    //         };

    //         $current = Contractable::where([
    //             'contractable_id' => $location->id,
    //             'contractable_type' => $location->getMorphClass(),
    //         ])->get();


    //         $currentContractIds = $current->pluck('contract_id')->toArray();
    //         Debugbar::info($currentContractIds);
    //         $toAttach = array_diff([$contract->id], $currentContractIds);
    //         Debugbar::info($toAttach);
    //         $toDetach = $current->whereIn('contract_id', array_diff($currentContractIds, [$contract->id]));
    //         Debugbar::info($toDetach);

    //         foreach ($toAttach as $contractId) {
    //             Contractable::create([
    //                 'contract_id' => $contractId,
    //                 'contractable_id' => $location->id,
    //                 'contractable_type' => $location->getMorphClass(),
    //             ]);
    //         }

    //         // dump('sync to detach');
    //         $toDetach->each->delete();
    //     }
    // }

    protected function syncContractables(Contract $contract, array $contractables)
    {
        $grouped = collect($contractables)
            ->filter(fn($item) => !empty($item['locationId']))
            ->groupBy('locationType');

        $types = [
            'site' => Site::class,
            'asset' => Asset::class,
            'building' => Building::class,
            'floor' => Floor::class,
            'room' => Room::class,
        ];

        foreach ($types as $type => $modelClass) {
            $newIds = $grouped->get($type, collect())->pluck('locationId')->toArray();

            // Récupérer les actuels
            $current = Contractable::where([
                'contract_id' => $contract->id,
                'contractable_type' => (new $modelClass)->getMorphClass(),
            ])->get();

            $currentIds = $current->pluck('contractable_id')->toArray();

            // À attacher
            foreach (array_diff($newIds, $currentIds) as $id) {
                Contractable::create([
                    'contract_id' => $contract->id,
                    'contractable_id' => $id,
                    'contractable_type' => (new $modelClass)->getMorphClass(),
                ]);
            }

            // À détacher
            $toDetach = $current->whereIn('contractable_id', array_diff($currentIds, $newIds));

            foreach ($toDetach as $contractableToDelete) {
                $contractableToDelete->delete();
            }
        }
    }



    // protected function syncContractables(Contract $contract, array $contractables)
    // {
    //     $groupedAssetLocations = collect($contractables)->groupBy('locationType')->map(function ($items) {
    //         return $items->pluck('locationId')->toArray();
    //     });

    //     $morphs = ['asset', 'site', 'building', 'floor', 'room'];

    //     foreach ($morphs as $morph) {
    //          $contract->{$morph . 's'}()->sync($groupedAssetLocations[$morph] ?? []);
    //     }

    //     return $contract;
    // }

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


    // MAILS
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
};
