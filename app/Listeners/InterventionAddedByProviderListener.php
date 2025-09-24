<?php

namespace App\Listeners;

use App\Models\Tenants\User;
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

            $users = User::role(['Admin'])->get();

            foreach ($users as $user) {
                dump($user->email);
                Mail::to($user->email)
                    ->locale($user->preferred_locale ?? config('app.locale'))
                    ->send(new InterventionAddedByProviderMail($event->intervention, $event->interventionAction));
            }

            if ($event->intervention->interventionable->manager) {
                Mail::to($event->intervention->maintainable->manager->email)
                    ->locale($user->preferred_locale ?? config('app.locale'))
                    ->send(new InterventionAddedByProviderMail($event->intervention, $event->interventionAction));
            }

        }
    }
}
