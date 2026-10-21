<?php

namespace App\Notifications;

use App\Domain\Notifications\Services\EmailTemplateService;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Same issue as VerifyEmailNotification: Laravel's default ResetPassword
 * notification hard-codes the route name 'password.reset', but our route
 * is actually named 'api.v1.auth.password.reset' (see routes/api.php).
 * This is an API — there's no web page to send the user to — so we build
 * a token/email query string instead of routing to a named route at all,
 * matching how a client (mobile app / SPA) would construct its own
 * "reset password" screen URL from these two values.
 */
class ResetPasswordNotification extends BaseResetPassword
{
    public function resetUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));

        return rtrim($frontendUrl, '/').'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        $rendered = app(EmailTemplateService::class)->render('password_reset', [
            'site_name' => config('app.name'),
            'customer_name' => $notifiable->name,
            'reset_url' => $this->resetUrl($notifiable),
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'] ?? 'Reset your password')
            ->view('emails.template', ['body' => $rendered['body'] ?? 'Please reset your password.']);
    }
}
