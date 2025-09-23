<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Mail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\InterventionAddedByProviderMail;
use App\Events\InterventionAddedByProviderEvent;

class InterventionAddedByProviderListener
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
    public function handle(InterventionAddedByProviderEvent $event): void
    {
        Debugbar::info($event->intervention, $event->intervention->load('interventionable'));
        if (env('APP_ENV') === "local") {
            Mail::to('crolweb@gmail.com')
                ->send(new InterventionAddedByProviderMail($event->intervention, $event->interventionAction));
        } else {
            Mail::to($event->email)
                ->send(new InterventionAddedByProviderMail($event->intervention, $event->interventionAction));
        }
    }
}
