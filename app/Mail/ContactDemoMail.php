<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactDemoMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $request
    ) {}

    public function envelope(): Envelope
    {

        return new Envelope(
            from: new Address('contact@sme-facility.com', 'SME-Facility - Contact'),
            subject: __(
                'website_demo.mail.contact.title',
                ['company' => $this->request['company']]
            ),
            replyTo: [
                new Address($this->request['email']),
            ],

        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demo',
        );
    }
}
