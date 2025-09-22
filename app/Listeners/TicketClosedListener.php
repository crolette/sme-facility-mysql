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
        Debugbar::info('TICKET CLOSED LISTENER');
        if (env('APP_ENV') !== "production") {

            if($event->ticket->being_notified) {
                $user = Auth::user();
    
                    Mail::to('crolweb@gmail.com')
                        ->locale(app()->getLocale())
                        ->send(new TicketClosedMail($event->ticket));
            }
        } else {
            if ($event->ticket->being_notified) {

                Mail::to($event->ticket->reporter_email)
                    ->locale($user->preferred_locale ?? config('app.locale'))
                    ->send(new TicketClosedMail($event->ticket));
            }
        }
    }
}
