<?php

namespace App\Domain\Notifications\Services;

use App\Support\SiteBrand;
use App\Domain\Notifications\Models\EmailTemplate;
use App\Domain\Notifications\Models\EmailLog;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    public function send(string $key, string $recipient, array $replacements = []): void
    {
        $rendered = $this->render($key, $replacements);
        if (! $rendered) {
            return;
        }

        $log = EmailLog::create([
            'template_key' => $key,
            'recipient' => $recipient,
            'subject' => $rendered['subject'],
            'status' => 'failed',
        ]);

        try {
            Mail::html($rendered['body'], function ($message) use ($recipient, $rendered): void {
                $message->to($recipient)->subject($rendered['subject']);
            });
        } catch (\Throwable $exception) {
            $log->update(['error' => $exception->getMessage()]);
            throw $exception;
        }

        $log->update(['status' => 'sent', 'sent_at' => now()]);
    }

    /**
     * @return array{subject: string, body: string}|null
     */
    public function render(string $key, array $replacements = []): ?array
    {
        $template = EmailTemplate::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return null;
        }

        $replacements['site_name'] ??= app(SiteBrand::class)->name();
        $replacements = $this->normalizedReplacements($replacements);

        return [
            'subject' => strtr($template->subject, $replacements),
            'body' => nl2br(e(strtr($template->body, $replacements))),
        ];
    }

    private function normalizedReplacements(array $replacements): array
    {
        return collect($replacements)->mapWithKeys(
            fn ($value, $key) => ['{{'.trim($key, '{} ').'}}' => (string) $value],
        )->all();
    }
}
