<?php

namespace Database\Seeders;

use App\Domain\Content\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title' => 'About Us', 'slug' => 'about-us', 'content' => '<h2>Making digital shopping feel simple</h2><p>We are building a dependable place to discover useful digital products without the confusion. Every product is presented with clear information, fair pricing and support you can count on.</p><p>From entertainment and productivity to digital tools and gift cards, our collection is curated around convenience, quality and everyday value.</p>'],
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'content' => '<h2>Your privacy matters</h2><p>We only collect information needed to process orders, provide support and improve your shopping experience. We do not sell personal information.</p>'],
            ['title' => 'Terms and Conditions', 'slug' => 'terms-and-conditions', 'content' => '<h2>Using our website</h2><p>By using this website, you agree to provide accurate information and use our products lawfully. Product availability and pricing may change.</p>'],
            ['title' => 'Shipping and Delivery', 'slug' => 'shipping-delivery', 'content' => '<h2>Delivery information</h2><p>Orders are processed after confirmation. Delivery timing depends on the selected method and destination. Our team will contact you when additional information is needed.</p>'],
            ['title' => 'Returns and Refunds', 'slug' => 'returns-refunds', 'content' => '<h2>Returns and refunds</h2><p>If you have an issue with an order, contact support with your order number. Eligible requests are reviewed according to product and payment conditions.</p>'],
            ['title' => 'Contact Us', 'slug' => 'contact-us', 'content' => '<h2>We are here to help</h2><p>Have a question about a product, payment or delivery? Our customer care team is ready to help you find a clear and convenient solution.</p><p>For order-related support, please include your order number and the product name so we can assist you faster.</p>'],
            ['title' => 'Frequently Asked Questions', 'slug' => 'faq', 'content' => json_encode([
                ['question' => 'How do I place an order?', 'answer' => 'Choose a product, add it to your cart, complete your delivery details and select a payment method at checkout.'],
                ['question' => 'How can I track my order?', 'answer' => 'Sign in to your account and open the order details page to view the latest order status.'],
                ['question' => 'What payment methods do you accept?', 'answer' => 'Available payment methods are shown securely during checkout, including cash on delivery and configured online payment options.'],
                ['question' => 'How long does delivery take?', 'answer' => 'Delivery time depends on your location and the selected shipping method. The applicable delivery charge is shown at checkout.'],
                ['question' => 'Can I request a return or refund?', 'answer' => 'Yes. Contact support with your order number and explain the issue so our team can review the return or refund request.'],
                ['question' => 'Can I change or cancel my order?', 'answer' => 'Contact us as soon as possible with your order number. We will try to help before the order is processed for delivery.'],
                ['question' => 'How can I become an eflix entrepreneur?', 'answer' => 'Open the Entrepreneur registration page, submit your details and wait for admin approval before accessing the entrepreneur dashboard.'],
                ['question' => 'Do I need to keep product stock?', 'answer' => 'No. You can promote selected products and submit customer orders through the available selling tools.'],
            ], JSON_UNESCAPED_UNICODE)],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], [...$page, 'is_published' => true, 'show_in_footer' => true]);
        }
    }
}
