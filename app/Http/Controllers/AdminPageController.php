<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPageController extends Controller
{
    public function index()
    {
        return view('admin.modules.pages', ['pages' => Page::query()->latest()->get()]);
    }

    public function edit(?Page $page = null)
    {
        $page ??= new Page(['is_published' => true, 'show_in_footer' => true]);
        $faqItems = [];
        if ($page->slug === 'faq' || $page->slug === 'frequently-asked-questions') {
            $faqItems = json_decode((string) $page->content, true) ?: $this->defaultFaqItems();
        }

        return view('admin.modules.page-form', compact('page', 'faqItems'));
    }

    public function save(Request $request, ?Page $page = null)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'content' => ['nullable', 'string', 'required_unless:slug,faq,frequently-asked-questions'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_footer' => ['nullable', 'boolean'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.question' => ['nullable', 'string', 'max:180'],
            'faq_items.*.answer' => ['nullable', 'string', 'max:600'],
        ]);
        if (in_array($data['slug'], ['faq', 'frequently-asked-questions'], true)) {
            $data['content'] = collect($data['faq_items'] ?? [])
                ->map(fn (array $item) => ['question' => trim($item['question'] ?? ''), 'answer' => trim($item['answer'] ?? '')])
                ->filter(fn (array $item) => $item['question'] !== '' && $item['answer'] !== '')
                ->values()
                ->toJson(JSON_UNESCAPED_UNICODE);
        }
        $data['is_published'] = $request->boolean('is_published');
        $data['show_in_footer'] = $request->boolean('show_in_footer');
        ($page ?? new Page())->fill($data)->save();

        return redirect()->route('admin.settings.pages')->with('success', 'Page saved successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return back()->with('success', 'Page deleted.');
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
}
