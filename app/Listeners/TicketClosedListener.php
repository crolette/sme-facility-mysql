<?php

namespace App\Listeners;

use App\Events\TicketClosed;
use App\Models\Tenants\User;
use App\Events\TicketCreated;
use App\Mail\TicketClosedMail;
use App\Mail\TicketCreatedMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketClosedListener
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
    public function handle(TicketClosed $event): void
    {
        // dump('TICKET CLOSED LISTENER');
        if (env('APP_ENV') === "local") {

            if ($event->ticket->being_notified) {
                $user = Auth::user();

                Mail::to('crolweb@gmail.com')
                    ->locale(app()->getLocale())
                    ->send(new TicketClosedMail($event->ticket));
            }
        } else {

            $admins = User::role('Admin')->get();

            if ($event->ticket->being_notified) {

                if (!$event->ticket->reporter || (!$admins->pluck('id')->contains($event->ticket->reporter?->id) && $event->ticket->ticketable->manager?->id !== $event->ticket->reporter?->id)) {

                    Mail::to($event->ticket->reporter_email)
                        ->locale($user->preferred_locale ?? config('app.locale'))
                        ->send(new TicketClosedMail($event->ticket));
                }
            }



            foreach ($admins as $admin) {
                Mail::to($admin->email)
                    ->locale($admin->preferred_locale ?? config('app.locale'))
                    ->send(new TicketClosedMail($event->ticket));
            }

            if ($event->ticket->ticketable->manager && !$admins->pluck('id')->contains($event->ticket->ticketable->manager?->id))
                Mail::to($event->ticket->ticketable->manager?->email)
                    ->locale($event->ticket->ticketable->manager->preferred_locale ?? config('app.locale'))
                    ->send(new TicketClosedMail($event->ticket));
        }
    }
}
