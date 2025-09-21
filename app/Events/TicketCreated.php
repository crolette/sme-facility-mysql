<?php

namespace App\Events;

use App\Models\Tenants\Ticket;
use App\Models\Tenants\Intervention;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TicketCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $model;

    /**
     * Create a new event instance.
     */
    /**
     * __construct
     *
     * @param  Ticket $ticket
     * @param  Model $model : is the model related to the ticket (asset, site, building, floor, room)
     * @return void
     */
    public function __construct(Ticket $ticket, Model $model)
    {
        Debugbar::info('TICKET CREATED EVENT');
        $this->ticket = $ticket;
        $this->model = $model;
    }
}
