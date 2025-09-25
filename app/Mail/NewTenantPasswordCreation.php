<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\Location;
use App\Models\Tenants\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewTenantPasswordCreation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Tenant $tenant,
        public string $url
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@sme-facility.com', "SME-Facility"),
            to:[$this->user->email],
            subject: 'Account created - Create your password ',

        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-tenant-password-creation',
        );
    }
}
