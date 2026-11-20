<?php

namespace App\Http\Controllers;

use App\Domain\Notifications\Models\EmailTemplate;
use App\Domain\Notifications\Models\EmailLog;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminEmailController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function smtp()
    {
        return view('admin.email.smtp', ['smtp' => $this->smtpSettings()]);
    }

    public function saveSmtp(Request $request)
    {
        $data = $request->validate([
            'mailer' => ['required', 'in:smtp,log'],
            'host' => ['required_if:mailer,smtp', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:mailer,smtp', 'nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:tls,ssl,none'],
            'from_address' => ['required', 'email', 'max:150'],
            'from_name' => ['required', 'string', 'max:100'],
        ]);

        foreach ([
            'mailer' => $data['mailer'],
            'host' => $data['host'] ?? '',
            'port' => $data['port'] ?? 2525,
            'username' => $data['username'] ?? '',
            'password' => filled($data['password'] ?? null) ? $data['password'] : $this->settings->get('mail.password', ''),
            'encryption' => ($data['encryption'] ?? 'tls') === 'none' ? '' : ($data['encryption'] ?? 'tls'),
            'from_address' => $data['from_address'],
            'from_name' => $data['from_name'],
        ] as $key => $value) {
            $this->settings->set("mail.{$key}", $value, 'mail', is_int($value) ? 'integer' : 'string', $request->user());
        }

        return back()->with('success', 'SMTP settings saved.');
    }

    public function sendSmtpTest(Request $request)
    {
        $data = $request->validate([
            'recipient' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
        ]);

        try {
            Mail::html($data['content'], function ($message) use ($data) {
                $message->to($data['recipient'])->subject($data['subject']);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'recipient' => 'Test email could not be sent. Check the SMTP host, port, encryption and credentials.',
            ])->withInput();
        }

        return back()->with('success', 'Test email sent to '.$data['recipient'].'.');
    }

    public function templates()
    {
        return view('admin.email.templates', [
            'templates' => EmailTemplate::query()->latest('id')->paginate(20),
        ]);
    }

    public function logs()
    {
        return view('admin.email.logs', [
            'logs' => EmailLog::query()->latest('id')->paginate(25),
        ]);
    }

    public function createTemplate()
    {
        return view('admin.email.template-form', ['template' => new EmailTemplate()]);
    }

    public function storeTemplate(Request $request)
    {
        EmailTemplate::create($this->templateData($request));
        return redirect()->route('admin.email.templates')->with('success', 'Email template created.');
    }

    public function editTemplate(EmailTemplate $template)
    {
        return view('admin.email.template-form', ['template' => $template]);
    }

    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        $template->update($this->templateData($request, $template));
        return redirect()->route('admin.email.templates')->with('success', 'Email template updated.');
    }

    public function destroyTemplate(EmailTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Email template deleted.');
    }

    public function sendTest(Request $request, EmailTemplate $template)
    {
        $data = $request->validate([
            'recipient' => ['required', 'email', 'max:150'],
        ]);

        $replacements = [
            '{{site_name}}' => app(\App\Support\SiteBrand::class)->name(),
            '{{customer_name}}' => $request->user()->name ?? 'Customer',
            '{{order_number}}' => 'ORD-10001',
            '{{grand_total}}' => '$129.00',
            '{{order_status}}' => 'Processing',
            '{{order_url}}' => url('/account/orders'),
            '{{verification_url}}' => url('/verify-email'),
            '{{reset_url}}' => url('/password/reset'),
            '{{login_url}}' => url('/account/login'),
            '{{reset_code}}' => '4827',
            '{{expires_minutes}}' => '60',
            '{{courier_name}}' => 'Demo Express',
            '{{tracking_number}}' => 'TRK-123456',
            '{{request_number}}' => 'RET-10001',
            '{{return_status}}' => 'Under review',
        ];

        $subject = strtr($template->subject, $replacements);
        $body = nl2br(e(strtr($template->body, $replacements)));

        Mail::html($body, function ($message) use ($data, $subject) {
            $message->to($data['recipient'])->subject('[Test] '.$subject);
        });

        return back()->with('success', 'Test email sent to '.$data['recipient'].'.');
    }

    private function templateData(Request $request, ?EmailTemplate $template = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9._-]+$/', $template ? 'unique:email_templates,key,'.$template->id : 'unique:email_templates,key'],
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function smtpSettings(): array
    {
        return [
            'mailer' => $this->settings->get('mail.mailer', config('mail.default')),
            'host' => $this->settings->get('mail.host', config('mail.mailers.smtp.host')),
            'port' => $this->settings->get('mail.port', config('mail.mailers.smtp.port')),
            'username' => $this->settings->get('mail.username', config('mail.mailers.smtp.username')),
            'password' => $this->settings->get('mail.password', ''),
            'encryption' => $this->settings->get('mail.encryption', 'tls'),
            'from_address' => $this->settings->get('mail.from_address', config('mail.from.address')),
            'from_name' => $this->settings->get('mail.from_name', config('mail.from.name')),
        ];
    }
}
