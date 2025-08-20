<?php

namespace App\Mail;

use App\Models\ScheduledNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $data;

    public function __construct(ScheduledNotification $notification)
    {
        $this->notification = $notification;
        $this->data = json_decode($notification->data, true) ?? [];
    }

    public function envelope(): Envelope
    {
        $subject = $this->getSubjectByType();

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.scheduled-notification',
            with: [
                'notification' => $this->notification,
                'data' => $this->data,
                'notificationType' => $this->notification->notification_type,
                'recipientName' => $this->notification->recipient_name,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function getSubjectByType(): string
    {
        return match ($this->notification->notification_type) {

            // types : maintenance, warranty, depreciation, contract, intervention
            'maintenance' => 'Maintenance programmée - ' . ($this->data['asset_name'] ?? 'Asset'),
            'warranty' => 'Fin de garantie prochaine - ' . ($this->data['asset_name'] ?? 'Asset'),
            'depreciation' => 'Fin de l\'amortissement - ' . ($this->data['asset_name'] ?? 'Asset'),
            'contract' => 'Expiration de contrat à venir - ' . ($this->data['contract_name'] ?? 'Contrat'),
            'intervention' => 'Intervention à prévoir - ' . ($this->data['contract_name'] ?? 'Contrat'),
            default => 'Notification - ' . $this->notification->notification_type
        };
    }
}
