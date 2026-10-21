<?php

namespace Database\Seeders;

use App\Domain\Notifications\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'welcome',
                'name' => 'Welcome email',
                'subject' => 'Welcome to {{site_name}}',
                'body' => "Hi {{customer_name}},\n\nWelcome to {{site_name}}. Your account is ready to use.\n\nLog in to your account: {{login_url}}\n\nThanks,\n{{site_name}}",
            ],
            [
                'key' => 'email_verification',
                'name' => 'Email verification',
                'subject' => 'Verify your email address',
                'body' => "Hi {{customer_name}},\n\nPlease verify your email address by clicking the link below:\n{{verification_url}}\n\nThis link expires in {{expires_minutes}} minutes.",
            ],
            [
                'key' => 'password_reset',
                'name' => 'Password reset',
                'subject' => 'Reset your {{site_name}} password',
                'body' => "Hi {{customer_name}},\n\nUse the link below to reset your password:\n{{reset_url}}\n\nIf you did not request this, you can ignore this email.",
            ],
            [
                'key' => 'password_reset_code',
                'name' => 'Password reset code',
                'subject' => '{{site_name}} password reset code',
                'body' => "Hi {{customer_name}},\n\nYour password reset code is: {{reset_code}}\n\nThis code expires in {{expires_minutes}} minutes. If you did not request this, you can ignore this email.",
            ],
            [
                'key' => 'order_placed',
                'name' => 'Order placed',
                'subject' => 'Order {{order_number}} received',
                'body' => "Hi {{customer_name}},\n\nWe received your order {{order_number}}.\nOrder total: {{grand_total}}\n\nWe will notify you when the status changes.",
            ],
            [
                'key' => 'order_status_updated',
                'name' => 'Order status updated',
                'subject' => 'Order {{order_number}} is now {{order_status}}',
                'body' => "Hi {{customer_name}},\n\nYour order {{order_number}} is now {{order_status}}.\n\nView order: {{order_url}}",
            ],
            [
                'key' => 'payment_confirmed',
                'name' => 'Payment confirmed',
                'subject' => 'Payment confirmed for order {{order_number}}',
                'body' => "Hi {{customer_name}},\n\nYour payment for order {{order_number}} has been confirmed.\n\nThank you for shopping with {{site_name}}.",
            ],
            [
                'key' => 'shipment_shipped',
                'name' => 'Shipment shipped',
                'subject' => 'Your order {{order_number}} has shipped',
                'body' => "Hi {{customer_name}},\n\nYour order {{order_number}} is on its way.\nCourier: {{courier_name}}\nTracking number: {{tracking_number}}",
            ],
            [
                'key' => 'return_request_updated',
                'name' => 'Return request updated',
                'subject' => 'Return request {{request_number}} updated',
                'body' => "Hi {{customer_name}},\n\nYour return request {{request_number}} is now {{return_status}}.\n\nView order: {{order_url}}",
            ],
            [
                'key' => 'review_submitted',
                'name' => 'Review submitted',
                'subject' => 'Your product review was submitted',
                'body' => "Hi {{customer_name}},\n\nYour review has been submitted and is waiting for moderation.\n\nThanks for your feedback.",
            ],
            [
                'key' => 'order_delivered',
                'name' => 'Order delivered',
                'subject' => 'Order {{order_number}} was delivered',
                'body' => "Hi {{customer_name}},\n\nYour order {{order_number}} has been delivered.\n\nWe hope you enjoy your purchase.",
            ],
            [
                'key' => 'campaign_promotion',
                'name' => 'Campaign · Promotional offer',
                'subject' => 'A special offer from {{site_name}}',
                'body' => "Hi {{customer_name}},\n\nWe have a special offer waiting for you.\n\nShop now: {{site_url}}\n\nThanks,\n{{site_name}}",
            ],
            [
                'key' => 'campaign_new_arrival',
                'name' => 'Campaign · New arrival',
                'subject' => 'New products have arrived at {{site_name}}',
                'body' => "Hi {{customer_name}},\n\nDiscover our latest products and find something you will love.\n\nExplore now: {{site_url}}\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_newsletter',
                'name' => 'Campaign · Newsletter',
                'subject' => 'News and updates from {{site_name}}',
                'body' => "Hi {{customer_name}},\n\nHere are the latest updates from {{site_name}}.\n\nVisit us: {{site_url}}\n\nYou received this email because you subscribed to our updates.",
            ],
            [
                'key' => 'campaign_category_amazon',
                'name' => 'Category · Amazon gift cards',
                'subject' => 'Shop Amazon gift cards at {{site_name}}',
                'body' => "Hi {{customer_name}},\n\nGive someone the freedom to choose with an Amazon gift card.\n\nBrowse now: {{site_url}}/shop?category=amajn\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_ai',
                'name' => 'Category · AI tools',
                'subject' => 'Work smarter with AI tools',
                'body' => "Hi {{customer_name}},\n\nExplore useful AI tools designed to help you create, learn and work faster.\n\nExplore AI tools: {{site_url}}/shop?category=ai-tools\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_entertainment',
                'name' => 'Category · Entertainment and streaming',
                'subject' => 'Your next entertainment experience is here',
                'body' => "Hi {{customer_name}},\n\nEnjoy premium entertainment and streaming access for your everyday moments.\n\nExplore entertainment: {{site_url}}/shop?category=entertainment\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_gift_cards',
                'name' => 'Category · Gift cards',
                'subject' => 'The perfect gift is just a click away',
                'body' => "Hi {{customer_name}},\n\nSend a thoughtful gift with our convenient digital gift cards.\n\nBrowse gift cards: {{site_url}}/shop?category=gift-cards\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_games',
                'name' => 'Category · Games',
                'subject' => 'Level up your gaming experience',
                'body' => "Hi {{customer_name}},\n\nDiscover games and digital products made for your next gaming session.\n\nExplore games: {{site_url}}/shop?category=games\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_netflix',
                'name' => 'Category · Netflix',
                'subject' => 'Make every night a movie night',
                'body' => "Hi {{customer_name}},\n\nEnjoy your favorite shows and movies with our Netflix-related digital products.\n\nExplore Netflix products: {{site_url}}/shop?category=netfliks\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_education',
                'name' => 'Category · Productivity and education',
                'subject' => 'Learn more and get more done',
                'body' => "Hi {{customer_name}},\n\nFind productivity and education products to support your goals.\n\nExplore productivity and education: {{site_url}}/shop?category=education\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_vpn',
                'name' => 'Category · VPN',
                'subject' => 'Browse secure VPN products',
                'body' => "Hi {{customer_name}},\n\nProtect your privacy and browse with confidence using our VPN products.\n\nExplore VPN: {{site_url}}/shop?category=vipien\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_music',
                'name' => 'Category · Music',
                'subject' => 'Bring more music into your day',
                'body' => "Hi {{customer_name}},\n\nExplore digital music products made for listeners and creators.\n\nBrowse music: {{site_url}}/shop?category=music\n\n{{site_name}}",
            ],
            [
                'key' => 'campaign_category_others',
                'name' => 'Category · Other products',
                'subject' => 'Explore more from {{site_name}}',
                'body' => "Hi {{customer_name}},\n\nDiscover more useful digital products from our collection.\n\nExplore more: {{site_url}}/shop?category=others\n\n{{site_name}}",
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['key' => $template['key']],
                [...$template, 'is_active' => true],
            );
        }
    }
}
