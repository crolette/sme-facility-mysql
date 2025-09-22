<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\SendInterventionToProviderEmail;
use App\Events\SendInterventionToProviderEvent;

class SendInterventionToProviderListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendInterventionToProviderEvent $event): void
    {
        if (env('APP_ENV') === "local") {
                Mail::to('crolweb@gmail.com')
                    ->send(new SendInterventionToProviderEmail($event->intervention, $event->url));
        } else {
                Mail::to($event->email)
                    ->send(new SendInterventionToProviderEmail($event->intervention, $event->url));
        }
    }
}
