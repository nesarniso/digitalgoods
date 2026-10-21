<?php

namespace App\Notifications;

use App\Domain\Notifications\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommerceNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly ?string $url = null,
        private readonly ?string $emailTemplate = null,
        private readonly array $emailReplacements = [],
    ) {}

    public function via(object $notifiable): array
    {
        return $this->emailTemplate && $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'url' => $this->url];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $rendered = app(EmailTemplateService::class)->render((string) $this->emailTemplate, $this->emailReplacements);
        $subject = $rendered['subject'] ?? $this->title;
        $body = $rendered['body'] ?? e($this->message);

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($subject)
            ->view('emails.template', ['body' => $body])
            ->action('View details', $this->url ?: url('/'));
    }
}
