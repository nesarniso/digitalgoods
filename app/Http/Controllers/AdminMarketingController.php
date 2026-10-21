<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\Models\MarketingCampaign;
use App\Domain\Marketing\Models\MarketingContact;
use App\Domain\Marketing\Models\MarketingContactList;
use App\Domain\Notifications\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminMarketingController extends Controller
{
    public function contacts()
    {
        return view('admin.marketing.contacts', [
            'lists' => MarketingContactList::query()->latest()->get(),
            'totalContacts' => MarketingContact::query()->count(),
        ]);
    }

    public function contactList(MarketingContactList $contactList)
    {
        return view('admin.marketing.contact-list', [
            'contactList' => $contactList,
            'contacts' => $contactList->contacts()->latest()->paginate(100),
        ]);
    }

    public function deleteContactList(MarketingContactList $contactList)
    {
        $name = $contactList->name;
        $contactList->delete();

        return back()->with('success', "{$name} deleted.");
    }

    public function campaigns()
    {
        return view('admin.marketing.campaigns', [
            'campaigns' => MarketingCampaign::query()->latest()->paginate(10),
            'defaultBody' => $this->defaultBody(),
            'customers' => User::query()
                ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'super_admin']))
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'contactLists' => MarketingContactList::query()->latest()->get(),
            'emailTemplates' => EmailTemplate::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'subject', 'body']),
        ]);
    }

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5242880'],
            'recipients' => ['nullable', 'string', 'max:200000'],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer', 'exists:users,id'],
            'all_customers' => ['nullable', 'boolean'],
            'subscriber_csv' => ['nullable', 'file', 'mimes:csv,txt', 'max:5120'],
            'contact_list_ids' => ['nullable', 'array'],
            'contact_list_ids.*' => ['integer', 'exists:marketing_contact_lists,id'],
        ]);

        $recipients = $this->parseRecipients($data['recipients'] ?? '');
        $customerQuery = User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'super_admin']))
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($request->boolean('all_customers')) {
            $recipients = array_merge($recipients, $customerQuery->pluck('email')->all());
        } elseif ($request->filled('customer_ids')) {
            $recipients = array_merge(
                $recipients,
                $customerQuery->whereIn('id', $data['customer_ids'])->pluck('email')->all(),
            );
        }

        if ($request->hasFile('subscriber_csv')) {
            $recipients = array_merge($recipients, $this->parseCsv($request->file('subscriber_csv')->getRealPath()));
        }
        if ($request->filled('contact_list_ids')) {
            $recipients = array_merge($recipients, MarketingContact::query()
                ->whereIn('contact_list_id', $data['contact_list_ids'])
                ->pluck('email')->all());
        }

        $recipients = array_values(array_unique(array_filter($recipients)));
        if ($recipients === []) {
            return back()->withErrors(['recipients' => 'Select customers, upload a CSV, or add at least one valid email address.'])->withInput();
        }

        $campaign = MarketingCampaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'recipients' => $recipients,
            'status' => 'sending',
            'contact_list_ids' => $data['contact_list_ids'] ?? [],
        ]);

        if (config('mail.default') !== 'smtp' || blank(config('mail.mailers.smtp.host'))) {
            $campaign->update([
                'status' => 'failed',
                'error_message' => 'SMTP is not configured. Save valid SMTP settings before sending a campaign.',
            ]);

            return back()->withErrors(['recipients' => 'Campaign was not sent because SMTP is not configured.'])->withInput();
        }

        $sentCount = 0;
        $errorMessage = null;
        foreach ($recipients as $recipient) {
            try {
                $replacements = [
                    '{{site_name}}' => config('app.name'),
                    '{{site_url}}' => config('app.url'),
                    '{{customer_name}}' => User::query()->where('email', $recipient)->value('name') ?: 'Customer',
                ];
                $subject = strtr($data['subject'], $replacements);
                $body = strtr($data['body'], $replacements);
                Mail::html($body, fn ($message) => $message->to($recipient)->subject($subject));
                $sentCount++;
            } catch (\Throwable $exception) {
                report($exception);
                $errorMessage ??= $exception->getMessage();
            }
        }

        $campaign->update([
            'status' => $sentCount === count($recipients) ? 'sent' : 'failed',
            'sent_count' => $sentCount,
            'error_message' => $errorMessage,
        ]);

        if ($sentCount !== count($recipients)) {
            return back()->withErrors(['recipients' => "Campaign delivery failed. {$sentCount} of ".count($recipients).' email(s) were sent.'])->withInput();
        }

        return back()->with('success', "Campaign sent to {$campaign->sent_count} recipient(s).");
    }

    public function importContacts(Request $request)
    {
        $data = $request->validate([
            'list_name' => ['required', 'string', 'max:120'],
            'contact_csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($request->file('contact_csv')->getRealPath(), 'rb');
        if ($handle === false) {
            return back()->withErrors(['contact_csv' => 'No valid email addresses found in this CSV.']);
        }

        $headers = fgetcsv($handle) ?: [];
        $emailIndex = collect($headers)->search(fn ($header) => strtolower(trim((string) $header)) === 'email');
        $nameIndex = collect($headers)->search(fn ($header) => strtolower(trim((string) $header)) === 'name');
        $chunk = [];
        $listNumber = 0;
        $created = 0;
        $seen = [];

        while (($row = fgetcsv($handle)) !== false) {
            $email = trim((string) ($emailIndex !== false ? ($row[$emailIndex] ?? '') : ($row[0] ?? '')));
            $normalized = strtolower($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || isset($seen[$normalized])) {
                continue;
            }
            $seen[$normalized] = true;
            $chunk[] = [
                'email' => $email,
                'name' => $nameIndex !== false ? trim((string) ($row[$nameIndex] ?? '')) : null,
            ];

            if (count($chunk) < 1000) {
                continue;
            }

            $listNumber++;
            $list = MarketingContactList::create([
                'name' => $data['list_name'].' '.$listNumber,
                'contact_count' => 1000,
            ]);
            $list->contacts()->createMany($chunk);
            $created += 1000;
            $chunk = [];
        }
        fclose($handle);

        if ($chunk !== []) {
            $listNumber++;
            $list = MarketingContactList::create([
                'name' => $data['list_name'].' '.$listNumber,
                'contact_count' => count($chunk),
            ]);
            $list->contacts()->createMany($chunk);
            $created += count($chunk);
        }

        if ($created === 0) {
            return back()->withErrors(['contact_csv' => 'No valid email addresses found in this CSV.']);
        }

        return back()->with('success', "{$created} contacts imported into {$listNumber} contact list(s) of up to 1,000 contacts.");
    }

    private function parseRecipients(string $input): array
    {
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $input, $matches);

        return array_values(array_unique(array_filter(
            $matches[0],
            fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        )));
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $emails = [];
        while (($row = fgetcsv($handle)) !== false) {
            foreach ($row as $value) {
                if (filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
                    $emails[] = trim($value);
                }
            }
        }
        fclose($handle);

        return $emails;
    }

    private function parseCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) return [];
        $headers = fgetcsv($handle) ?: [];
        $emailIndex = collect($headers)->search(fn ($header) => strtolower(trim((string) $header)) === 'email');
        $nameIndex = collect($headers)->search(fn ($header) => strtolower(trim((string) $header)) === 'name');
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $email = trim((string) ($emailIndex !== false ? ($row[$emailIndex] ?? '') : ($row[0] ?? '')));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rows[strtolower($email)] = ['email' => $email, 'name' => $nameIndex !== false ? trim((string) ($row[$nameIndex] ?? '')) : null];
            }
        }
        fclose($handle);
        return array_values($rows);
    }

    private function defaultBody(): string
    {
        $appName = e(config('app.name'));

        return <<<HTML
<!doctype html>
<html lang="en">
<body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;background:#f1f5f9">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 12px 30px rgba(15,23,42,.10)">
        <tr><td style="padding:30px 36px;background:#4f46e5;color:#ffffff">
          <div style="font-size:13px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">{$appName}</div>
          <h1 style="margin:16px 0 0;font-size:28px;line-height:1.25">A special message for you</h1>
        </td></tr>
        <tr><td style="padding:36px">
          <p style="margin:0 0 16px;font-size:16px;line-height:1.7">Hello {{customer_name}},</p>
          <p style="margin:0 0 24px;font-size:16px;line-height:1.7;color:#475569">We have something special prepared for you. Discover our latest updates, products and exclusive offers.</p>
          <a href="{{shop_url}}" style="display:inline-block;padding:13px 22px;border-radius:10px;background:#4f46e5;color:#ffffff;text-decoration:none;font-weight:bold">Explore now</a>
          <p style="margin:30px 0 0;font-size:12px;line-height:1.6;color:#94a3b8">You are receiving this email from {$appName}. If you no longer wish to receive these messages, <a href="{{unsubscribe_url}}" style="color:#4f46e5">unsubscribe here</a>.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }
}
