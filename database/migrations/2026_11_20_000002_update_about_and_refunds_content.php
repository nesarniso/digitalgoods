<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_ABOUT = '<h2>Making digital shopping feel simple</h2><p>We are building a dependable place to discover useful digital products without the confusion. Every product is presented with clear information, fair pricing and support you can count on.</p><p>From entertainment and productivity to digital tools and gift cards, our collection is curated around convenience, quality and everyday value.</p>';

    private const DEFAULT_REFUNDS = '<h2>Returns and refunds</h2><p>If you have an issue with an order, contact support with your order number. Eligible requests are reviewed according to product and payment conditions.</p>';

    private const ABOUT_CONTENT = <<<'HTML'
<p>WorkLinc offers a diverse range of services and products to meet all your digital needs.</p>
<p>Welcome to WorkLinc, your go-to hub for all things digital. We're here to make your online experience smoother and more exciting. Explore our wide range of innovative products designed to boost your productivity and enhance your online presence.</p>
<hr>
<h2>Everything You Need, Made Digital.</h2>
<p>WorkLinc brings together useful, creative and ready-to-use digital resources designed to make your work faster, easier and more productive.</p>
<ol>
<li><strong>Ready to Use</strong><br>Get practical digital resources that are ready to download and use without unnecessary complications.</li>
<li><strong>Curated Collections</strong><br>We bring together large collections and bundles so you can get more useful resources in one place.</li>
<li><strong>Creative Resources</strong><br>Discover creative digital assets designed for creators, marketers, businesses and everyday digital work.</li>
<li><strong>Instant Access</strong><br>Once your purchase is completed, access your digital products quickly and conveniently.</li>
</ol>
<h2>Built For People Who Create.</h2>
<p>Digital work shouldn't always start from scratch. WorkLinc helps you save time by giving you access to useful resources that can become part of your creative workflow.</p>
<ul>
<li><strong>Save Time</strong> — Spend less time searching and more time creating.</li>
<li><strong>More Resources</strong> — Explore carefully packaged digital collections and bundles.</li>
<li><strong>Simple Experience</strong> — We keep the buying and accessing experience simple and straightforward.</li>
<li><strong>Digital First</strong> — Everything is built around modern digital workflows.</li>
</ul>
<h2>A Growing World of Digital Resources.</h2>
<p>Explore different types of digital products created to support creativity, content creation, productivity and online growth.</p>
<ul>
<li><strong>AI Reels &amp; Videos</strong> — Ready-to-use video and reels resources for modern content creators.</li>
<li><strong>Graphics &amp; Creative Packs</strong> — Creative assets and visual resources for your projects and campaigns.</li>
<li><strong>Premium Bundles</strong> — Large collections packed with useful digital resources.</li>
<li><strong>Creative Tools</strong> — Digital resources designed to simplify your creative workflow.</li>
</ul>
<h2>Simple. Useful. Digital.</h2>
<p>Our goal is simple — make quality digital resources easier to discover, easier to access and more useful for the people who need them.</p>
<p>As WorkLinc continues to grow, we're constantly exploring new ideas, products and resources to make your digital journey even better.</p>
HTML;

    private const REFUNDS_CONTENT = <<<'HTML'
<p>Thank you for shopping with <strong>WorkLinc</strong>. We aim to provide the highest quality digital products and services. Please read our Refund &amp; Returns Policy carefully before placing an order.</p>
<hr>
<h2>1. Digital Products (Non-Refundable)</h2>
<p>All products sold on WorkLinc — including digital bundles, reels packs, templates, graphics, software, and all downloadable content — are <strong>non-refundable</strong>.</p>
<p>We do not offer refunds once a digital product is delivered or downloaded because:</p>
<ul><li>Digital files cannot be returned.</li><li>Access to downloads cannot be revoked.</li><li>Delivery is instant after payment.</li></ul>
<p>By completing your purchase, you agree to our non-refund policy.</p>
<h2>2. Wrong Product / File Issues</h2>
<p>If you receive:</p>
<ul><li>A corrupted or damaged file</li><li>A missing or incomplete file</li><li>An incorrect product</li><li>A broken/non-working download link</li></ul>
<p>Please contact us within <strong>48 hours</strong> of receiving your order. Use the email address or phone number on our <a href="/contact-us">Contact Us page</a> and include your order number.</p>
<h2>3. Duplicate Payment</h2>
<p>A refund will be issued if:</p>
<ul><li>You were charged twice for the same order.</li><li>A technical error caused accidental double payment.</li></ul>
<p>Refund time: <strong>5–7 business days</strong> after payment verification.</p>
<h2>4. Order Not Received</h2>
<p>If you did not receive the download link or cannot access your files, contact us anytime. We will re-send your order immediately.</p>
<h2>5. Change of Mind – Not Eligible</h2>
<p>Refunds will <strong>not</strong> be issued for the following reasons:</p>
<ul><li>“I didn’t like the product”</li><li>“I don’t need it anymore”</li><li>“I bought it by mistake”</li><li>“It didn’t meet my expectations”</li></ul>
<p>All digital purchases are final.</p>
<h2>6. Support &amp; Assistance</h2>
<p>We provide complete support to ensure your files work properly. If you face any issue, our team is ready to assist. Visit our <a href="/contact-us">Contact Us page</a> for current support details.</p>
<h2>7. Agreement</h2>
<p>By using our website and purchasing digital products, you confirm that you have read and agreed to this Refund &amp; Returns Policy.</p>
HTML;

    public function up(): void
    {
        DB::table('pages')
            ->where('slug', 'about-us')
            ->where('content', self::DEFAULT_ABOUT)
            ->update(['content' => self::ABOUT_CONTENT, 'updated_at' => now()]);

        DB::table('pages')
            ->where('slug', 'returns-refunds')
            ->where('content', self::DEFAULT_REFUNDS)
            ->update(['title' => 'Refund & Returns', 'content' => self::REFUNDS_CONTENT, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('pages')
            ->where('slug', 'about-us')
            ->where('content', self::ABOUT_CONTENT)
            ->update(['content' => self::DEFAULT_ABOUT, 'updated_at' => now()]);

        DB::table('pages')
            ->where('slug', 'returns-refunds')
            ->where('content', self::REFUNDS_CONTENT)
            ->update(['title' => 'Returns and Refunds', 'content' => self::DEFAULT_REFUNDS, 'updated_at' => now()]);
    }
};
