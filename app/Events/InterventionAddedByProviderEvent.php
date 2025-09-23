<?php

namespace App\Events;

use App\Models\Tenants\Intervention;
use App\Models\Tenants\InterventionAction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InterventionAddedByProviderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $intervention;
    public $interventionAction;

    /**
     * Create a new event instance.
     */
    public function __construct(Intervention $intervention, InterventionAction $interventionAction)
    {
        $this->intervention = $intervention;
        $this->interventionAction = $interventionAction;
    }


}
