<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Tenants\ScheduledNotification;

class ScheduledNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $data;

    public function __construct(ScheduledNotification $notification)
    {
        $this->notification = $notification;
        $this->data = $notification->data ?? [];
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
            'next_maintenance_date' => 'Maintenance programmée - ' . ($this->data['subject'] ?? 'Maintenance'),
            'end_warranty_date' => 'Fin de garantie prochaine - ' . ($this->data['subject'] ?? 'Maintenance'),
            'depreciation_end_date' => 'Fin de l\'amortissement - ' . ($this->data['subject'] ?? 'Asset'),
            'end_date' => 'Expiration de contrat à venir - ' . ($this->data['subject'] ?? 'Contrat'),
            'notice_date' => 'Contrat délai de préavis - ' . ($this->data['subject'] ?? 'Contrat'),
            'planned_at' => 'Intervention à prévoir - ' . ($this->data['subject'] ?? 'Intervention') . ' - ' . ($this->data['priority'] ?? ''),
            default => 'Notification - ' . $this->notification->notification_type
        };
    }
}
