<?php

namespace App\Listeners;

use App\Models\Tenants\User;
use App\Events\TicketCreated;
use App\Mail\TicketCreatedMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketCreatedListener
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
    public function handle(TicketCreated $event): void
    {
        Debugbar::info('TICKET CREATED LISTENER');
        if (env('APP_ENV') === "local") {

            $user = Auth::user();

                Mail::to('crolweb@gmail.com')
                    ->locale($user->preferred_locale ?? config('app.locale'))
                    ->send(new TicketCreatedMail($event->ticket, $event->model));
        } else {

            $users = User::role(['Admin'])->get();

            foreach ($users as $user) {
                Mail::to($user->email)
                    ->locale($user->preferred_locale ?? config('app.locale'))
                    ->send(new TicketCreatedMail($event->ticket, $event->model));
            }

            if($event->ticket->ticketable->manager) {
                Mail::to($event->ticket->ticketable->manager->email)
                    ->locale(config('app.locale'))
                    ->send(new TicketCreatedMail($event->ticket, $event->model));
            }
        }
    }
}
