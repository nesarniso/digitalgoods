<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\ContactMessage;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function show(Page $page, SettingService $settings)
    {
        abort_unless($page->is_published, 404);

        if ($page->slug === 'contact-us') {
            return view('storefront.page', [
                'page' => $page,
                'contactSettings' => $this->contactSettings($settings),
            ]);
        }
        if (in_array($page->slug, ['faq', 'frequently-asked-questions'], true)) {
            return view('storefront.page', [
                'page' => $page,
                'faqItems' => json_decode((string) $page->content, true) ?: $this->defaultFaqItems(),
            ]);
        }

        return view('storefront.page', compact('page'));
    }

    public function contactPage(SettingService $settings)
    {
        $page = Page::query()->where('slug', 'contact-us')->first()
            ?? new Page([
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<p>Our customer care team is ready to help with products, payments, delivery and orders.</p>',
                'is_published' => true,
            ]);

        $contactSettings = $this->contactSettings($settings);

        return view('storefront.page', compact('page', 'contactSettings'));
    }

    public function aboutPage(SettingService $settings)
    {
        return $this->show(Page::query()->where('slug', 'about-us')->firstOrFail(), $settings);
    }

    public function refundsPage(SettingService $settings)
    {
        return $this->show(Page::query()->where('slug', 'returns-refunds')->firstOrFail(), $settings);
    }

    private function contactSettings(SettingService $settings): array
    {
        $faqItems = json_decode((string) $settings->get('contact.faq_items', ''), true);

        return [
            'badge' => $settings->get('contact.badge', 'Get in touch'),
            'hero_title' => $settings->get('contact.hero_title', "We're Here To Help You."),
            'hero_text' => $settings->get('contact.hero_text', 'Have a question about our digital products? Need assistance with your purchase? Our team is ready to help.'),
            'form_eyebrow' => $settings->get('contact.form_eyebrow', 'Send a message'),
            'form_title' => $settings->get('contact.form_title', 'Need a little help?'),
            'form_text' => $settings->get('contact.form_text', "Whether it's a product question, download assistance, or anything related to your purchase, don't hesitate to reach out."),
            'support_hours' => $settings->get('contact.support_hours', 'Saturday–Thursday, 10:00 AM–8:00 PM (BST)'),
            'response_time' => $settings->get('contact.response_time', 'We usually reply within 24 hours.'),
            'email' => $settings->get('system.email', ''),
            'phone' => $settings->get('system.phone', ''),
            'whatsapp' => $settings->get('system.whatsapp', ''),
            'address' => $settings->get('system.address', ''),
            'faq_eyebrow' => $settings->get('contact.faq_eyebrow', 'Quick answers'),
            'faq_title' => $settings->get('contact.faq_title', 'Before you contact us'),
            'faq_items' => is_array($faqItems) ? $faqItems : [
                ['question' => 'How do I track my order?', 'answer' => 'Keep your order number ready and choose Order support in the form above.'],
                ['question' => 'Can I request a return or refund?', 'answer' => 'Yes. Select Returns and refunds and include your order number and the reason.'],
                ['question' => 'What payment methods do you accept?', 'answer' => 'Our available payment options are shown securely during checkout.'],
            ],
        ];
    }

    private function defaultFaqItems(): array
    {
        return [
            ['question' => 'How do I place an order?', 'answer' => 'Choose a product, add it to your cart, complete your delivery details and select a payment method at checkout.'],
            ['question' => 'How can I track my order?', 'answer' => 'Sign in to your account and open the order details page to view the latest order status.'],
            ['question' => 'What payment methods do you accept?', 'answer' => 'Available payment methods are shown securely during checkout, including cash on delivery and configured online payment options.'],
            ['question' => 'How long does delivery take?', 'answer' => 'Delivery time depends on your location and the selected shipping method. The applicable delivery charge is shown at checkout.'],
            ['question' => 'Can I request a return or refund?', 'answer' => 'Yes. Contact support with your order number and explain the issue so our team can review the return or refund request.'],
            ['question' => 'Can I change or cancel my order?', 'answer' => 'Contact us as soon as possible with your order number. We will try to help before the order is processed for delivery.'],
            ['question' => 'How can I become an eflix entrepreneur?', 'answer' => 'Open the Entrepreneur registration page, submit your details and wait for admin approval before accessing the entrepreneur dashboard.'],
            ['question' => 'Do I need to keep product stock?', 'answer' => 'No. You can promote selected products and submit customer orders through the available selling tools.'],
        ];
    }

    public function contact(Request $request, SettingService $settings): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'topic' => ['required', 'string', 'in:Order support,Product question,Payment,Returns and refunds,General inquiry'],
            'order_number' => ['nullable', 'string', 'max:80'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $recipient = trim((string) $settings->get('system.email', config('mail.from.address')));
        abort_unless(filter_var($recipient, FILTER_VALIDATE_EMAIL), 503, 'Contact email is not configured.');

        ContactMessage::create($data);
        Mail::html(view('emails.contact-request', ['data' => $data])->render(), function ($mail) use ($recipient, $data): void {
            $mail->to($recipient)
                ->replyTo($data['email'], $data['name'])
                ->subject('New contact request: '.$data['topic']);
        });

        return back()->with('contact_success', 'Thank you. Your message has been sent and our team will reply within 24 hours.');
    }
}
