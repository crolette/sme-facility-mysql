<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Tenants\Ticket;
use Illuminate\Support\Facades\App;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public Model $model
    ) {

        $locale = App::getLocale();
        App::setLocale($locale);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('notifications@sme-facility.com', 'SME-Facility - Notification'),
            subject: __('actions.new-type', ['type' => trans_choice('tickets.title', 1)]) . ' : ' . $this->ticket->code . ' - ' . $this->model->name,

        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        //    dump('PICTURES TICKETS', isset($this->ticket->pictures[2]) ? 'PICTURE' : 'NO PICTURE');

        return array_filter([
            isset($this->ticket->pictures[0])
                ? Attachment::fromStorageDisk('tenants',  $this->ticket->pictures[0]->path)
                : null,
            isset($this->ticket->pictures[1])
                ? Attachment::fromStorageDisk('tenants',  $this->ticket->pictures[1]->path)
                : null,
            isset($this->ticket->pictures[2])
                ? Attachment::fromStorageDisk('tenants',  $this->ticket->pictures[2]->path)
                : null,
        ]);
    }
}
