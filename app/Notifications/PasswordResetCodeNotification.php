<?php

namespace App\Notifications;

use App\Domain\Notifications\Services\EmailTemplateService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class PasswordResetCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rendered = app(EmailTemplateService::class)->render('password_reset_code', [
            'site_name' => config('app.name'),
            'customer_name' => $notifiable->name,
            'reset_code' => $this->code,
            'expires_minutes' => '10',
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'] ?? 'Your '.config('app.name').' password reset code')
            ->view('emails.template', [
                'body' => $rendered['body'] ?? view('emails.password-reset-code', [
                    'name' => $notifiable->name,
                    'code' => $this->code,
                    'expires' => 10,
                ])->render(),
            ]);
    }
}
