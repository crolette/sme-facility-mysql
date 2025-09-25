<?php

namespace App\Listeners;

use App\Mail\NewTenantCreatedMail;
use Illuminate\Support\Facades\Mail;
use App\Events\NewTenantCreatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewTenantCreatedListener
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
    public function handle(NewTenantCreatedEvent $event): void
    {
        if (env('APP_ENV') === "local") {
            Mail::to('crolweb@gmail.com')
                ->send(new NewTenantCreatedMail($event->user, $event->tenant));
        } else {
            Mail::to($event->email)
                ->send(new NewTenantCreatedMail($event->user, $event->tenant));
        }
    }
}
