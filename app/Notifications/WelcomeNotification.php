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
        $siteName = app(\App\Support\SiteBrand::class)->name();
        $rendered = app(EmailTemplateService::class)->render('welcome', [
            'site_name' => $siteName,
            'customer_name' => $notifiable->name,
            'login_url' => url('/account/login'),
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'] ?? 'Welcome to '.$siteName)
            ->view('emails.template', [
                'body' => $rendered['body'] ?? view('emails.welcome', [
                    'siteName' => $siteName,
                    'name' => $notifiable->name,
                    'loginUrl' => url('/account/login'),
                ])->render(),
            ]);
    }
}
