<?php

namespace App\Notifications;

use App\Domain\Notifications\Services\EmailTemplateService;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

/**
 * Laravel's built-in VerifyEmail notification hard-codes the route name
 * 'verification.verify'. Our API routes are versioned/namespaced (see
 * routes/api.php), so the real route name is 'api.v1.auth.verification.verify'.
 * Without this override, registration throws a RouteNotFoundException the
 * moment the verification email is queued — see AuthService::registerCustomer().
 */
class VerifyEmailNotification extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'api.v1.auth.verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $rendered = app(EmailTemplateService::class)->render('email_verification', [
            'site_name' => config('app.name'),
            'customer_name' => $notifiable->name,
            'verification_url' => $this->verificationUrl($notifiable),
            'expires_minutes' => config('auth.verification.expire', 60),
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'] ?? 'Verify your email address')
            ->view('emails.template', ['body' => $rendered['body'] ?? 'Please verify your email address.']);
    }
}
