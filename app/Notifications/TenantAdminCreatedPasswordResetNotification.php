<?php

namespace App\Notifications;

use App\Mail\NewTenantPasswordCreation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TenantAdminCreatedPasswordResetNotification extends ResetPassword
{
    // use Queueable;

    protected $tenant;

    public function __construct($token, $tenant)
    {
        parent::__construct($token);
        $this->tenant = $tenant;
    }

    protected function resetUrl($notifiable)
    {
        // $tenantUrl = 'https://' . $this->tenant->domain->domain;
        
        // Log::info('Tenant URL Reset Password : ' . $tenantUrl . '.sme-facility.com/reset-password/' . $this->token . '?' . http_build_query([
        //     'email' => $notifiable->getEmailForPasswordReset(),
        // ]));

        if(env('APP_ENV') === "production") {
            $tenantUrl = 'https://' . $this->tenant->domain->domain;
            return $tenantUrl . '.sme-facility.com/reset-password/' . $this->token . '?' . http_build_query([
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        } else {
            $tenantUrl = 'http://' . $this->tenant->domain->domain;
            return $tenantUrl . '.localhost:8000/reset-password/' . $this->token . '?' . http_build_query([
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

        }
    }

    public function toMail($notifiable)
    {
        return new NewTenantPasswordCreation(
            $notifiable,
            $this->tenant,
            $this->resetUrl($notifiable)
        );
    }
    
}
