<?php

namespace App\Notifications;

use App\Domain\Notifications\Services\EmailTemplateService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rendered = app(EmailTemplateService::class)->render('welcome', [
            'site_name' => config('app.name'),
            'customer_name' => $notifiable->name,
            'login_url' => url('/account/login'),
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'] ?? 'Welcome to '.config('app.name'))
            ->view('emails.template', [
                'body' => $rendered['body'] ?? view('emails.welcome', [
                    'siteName' => config('app.name'),
                    'name' => $notifiable->name,
                    'loginUrl' => url('/account/login'),
                ])->render(),
            ]);
    }
}
