<?php

namespace App\Events;

use App\Models\Tenants\Intervention;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendInterventionToProviderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $intervention;
    public $email;
    public $url;

    /**
     * Create a new event instance.
     */
    public function __construct(Intervention $intervention, string $email, string $url)
    {
        $this->intervention = $intervention;
        $this->email = $email;
        $this->url = $url;
    }


}
