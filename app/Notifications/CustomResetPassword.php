<?php

namespace App\Notifications;

use App\Mail\PasswordReset;
use App\Models\Tenants\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    use Queueable;


    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('SME-Facility - Reset password')
            ->view('emails.password_reset', [
                'user' => $notifiable,
                'url' => url(route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false))
            ]);



        // ->subject('Reset Your Password')
        // ->greeting("Hi there!")
        // ->line('Forgot your password? No problem! Click the button below to reset it.')
        // ->action('Reset Password', url(route('password.reset', [
        //     'token' => $this->token,
        //     'email' => $notifiable->getEmailForPasswordReset(),
        // ], false)))
        // ->line('This password reset link will expire in :count minutes.', [
        //     'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')
        // ])
        // ->line('If you didn\'t request this, you can safely ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
