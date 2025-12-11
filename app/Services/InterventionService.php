<?php

namespace App\Services;

use App\Events\TicketClosed;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Provider;
use App\Services\DocumentService;
use App\Models\Tenants\Intervention;

class InterventionService
{

    public function __construct(protected DocumentService $documentService) {}

    public function create($data): Intervention
    {
        $intervention = new Intervention(
            $data
        );

        if (isset($data['ticket_id']))
            $intervention = $this->associateInterventionToTicket($data['ticket_id'], $intervention);
        else
            $intervention = $this->associateInterventionToModel($data, $intervention);


        $intervention->interventionType()->associate($data['intervention_type_id']);
        $intervention->save();

        return $intervention;
    }

    public function associateInterventionToTicket($ticketId, $intervention): Intervention
    {
        $ticket = Ticket::find($ticketId);
        $intervention->ticket()->associate($ticket);
        $intervention->interventionable()->associate($ticket->ticketable);
        $intervention->maintainable()->associate($ticket->ticketable->maintainable->id);

        return $intervention;
    }

    public function associateInterventionToModel($data, $intervention)
    {
        $modelMap = [
            'sites' => \App\Models\Tenants\Site::class,
            'buildings' => \App\Models\Tenants\Building::class,
            'floors' => \App\Models\Tenants\Floor::class,
            'rooms' => \App\Models\Tenants\Room::class,
            'asset' => \App\Models\Tenants\Asset::class,
            'providers' => \App\Models\Tenants\Provider::class,
        ];


        $model = $modelMap[$data['locationType']];

        if ($model === Provider::class) {
            $intervention = $this->associateInterventionToProvider($data['locationId'], $intervention);
        } else {
            $intervention = $this->associateInterventionToAssetOrLocation($model, $data['locationId'], $intervention);
        }

        return $intervention;
    }

    public function associateInterventionToProvider($providerId, $intervention): Intervention
    {
        $location = Provider::where('id', $providerId)->first();
        $intervention->interventionable()->associate($location);

        return $intervention;
    }

    public function associateInterventionToAssetOrLocation($model, $locationId, $intervention): Intervention
    {
        $location = $model::where('reference_code', $locationId)->first();
        $intervention->interventionable()->associate($location);
        $intervention->maintainable()->associate($location->maintainable->id);

        return $intervention;
    }

    public function update($intervention, $data): Intervention
    {
        $intervention->interventionType()->associate($data['intervention_type_id']);

        $data = $this->handleStatusChange($intervention, $data);

        $intervention->fill($data);
        $intervention->save();

        return $intervention;
    }

    public function delete($intervention): bool
    {
        $deleted = $intervention->delete();
        return $deleted;
    }

    public function handleStatusChange(Intervention $intervention, array $data): array
    {
        if (
            isset($data['status']) &&
            $data['status'] === 'completed' &&
            $intervention->status !== 'completed' && !isset($intervention->completed_at)
        ) {
            $data['completed_at'] = now();
        }

        if (
            isset($data['status']) &&
            $data['status'] === 'cancelled' &&
            $intervention->status !== 'cancelled' && !isset(
                $intervention->cancelled_at
            )
        ) {
            $data['cancelled_at'] = now();
        }


        return $data;
    }
};
