<section id="complete-reseller-details" class="space-y-6" aria-labelledby="reseller-plan-details-title">
    <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-rose-950 p-6 text-white shadow-xl sm:p-10">
        <div class="absolute -right-12 -top-20 h-56 w-56 rounded-full bg-rose-500/20 blur-2xl" aria-hidden="true"></div>
        <div class="relative">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-orange-300">👑 Premium Reseller Access</p>
            <h2 id="reseller-plan-details-title" class="mt-4 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl">Your Brand. Your Prices. Your Business.</h2>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-200 sm:text-base">Access premium digital product bundles with eligible reseller rights and start building your own digital products business. Sell ready-made digital content under your brand, subject to licence terms.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach(['All Personal Plan Benefits', 'Reseller Licence', 'Your Own Pricing', 'Branding Rights'] as $tag)
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-white">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </header>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="reseller-personal-benefits-title">
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-600">Included with reseller access</p>
        <h3 id="reseller-personal-benefits-title" class="mt-2 text-2xl font-black text-slate-900">🎁 Personal Plan Benefits Included!</h3>
        <p class="mt-2 text-sm text-slate-500">Enjoy the creative resources of the Personal Use plan along with additional reseller privileges.</p>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                'Ready-to-Post Reels, Shorts & Videos',
                'HD Quality Digital Content',
                'Watermark-Free Content',
                'Multiple Content Categories & Niches',
                'Creative Assets & Editable Templates',
                'Hindi & English Content',
                'Organised & Easy Downloads',
                'Mobile & Desktop Access',
                'Eligible Bundle Updates',
                'Download Assistance',
            ] as $benefit)
                <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-black text-emerald-700" aria-hidden="true">✓</span>
                    {{ $benefit }}
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-orange-100 bg-orange-50/50 p-6 sm:p-8" aria-labelledby="reseller-benefits-title">
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">Exclusive reseller benefits</p>
        <h3 id="reseller-benefits-title" class="mt-2 text-2xl font-black text-slate-900">More Products. More Ways to Sell. 🚀</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">Enjoy additional reseller benefits that help you market digital products and build your own business.</p>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['📜', 'Reseller Licence Included', 'Get resale permissions for eligible digital products included in your reseller plan, subject to the applicable licence conditions.'],
                ['🌐', 'Sell on Your Own Website', 'List and sell eligible digital products through your own website, online store or other permitted sales channels.'],
                ['💰', 'Set Your Own Selling Prices', 'Choose your own product prices, create promotional offers and manage your retail pricing.'],
                ['🎨', 'Branding & Customisation Rights', 'Customise and rebrand eligible digital products according to the permissions included in your reseller licence.'],
                ['🖼️', 'Ready-to-Use Product Creatives', 'Save time on marketing with available promotional creatives and digital product resources.'],
                ['📦', 'Flexible Resale Options', 'Sell eligible products separately or package them into your own digital bundles wherever permitted by licence terms.'],
                ['📚', 'Customer Support Resources', 'Access available product information, guides and support resources to help with product-related queries.'],
                ['⭐', 'Priority Customer Support', 'Receive prioritised assistance for reseller plan queries, digital product access and related support requests.'],
            ] as [$icon, $title, $description])
                <article class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                    <h4 class="mt-3 font-extrabold text-slate-900">{{ $title }}</h4>
                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-6 sm:p-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-600">Build your own digital products business</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">A Business That’s Yours</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">A practical solution for entrepreneurs who want to start selling digital products under their own brand.</p>
            <div class="mt-5 space-y-3">
                @foreach([
                    ['🏪', 'Your Own Store', 'Create an online storefront and sell eligible digital product bundles.'],
                    ['🏷️', 'Your Own Brand', 'Promote permitted digital products using your own branding.'],
                    ['📈', 'Your Own Pricing', 'Manage product pricing and develop your own sales strategy.'],
                ] as [$icon, $title, $description])
                    <article class="flex gap-3 rounded-2xl bg-white p-4">
                        <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                        <div>
                            <h4 class="font-extrabold text-slate-900">{{ $title }}</h4>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-6 sm:p-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-emerald-700">Start selling digital products</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">Who Is This Plan Perfect For?</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Designed for individuals and businesses interested in digital product reselling.</p>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach(['💼 Entrepreneurs', '🏪 Online Store Owners', '📦 Digital Product Sellers', '📣 Digital Marketers', '🎨 Creative Agencies', '💻 Freelancers', '🚀 Startup Businesses', '📱 Social Media Sellers'] as $audience)
                    <span class="rounded-full border border-emerald-100 bg-white px-3 py-2 text-xs font-bold text-slate-700">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="reseller-steps-title">
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">Get started</p>
        <h3 id="reseller-steps-title" class="mt-2 text-2xl font-black text-slate-900">Start Your Reseller Journey in 3 Steps</h3>
        <p class="mt-2 text-sm text-slate-500">A simple way to begin building your digital products business.</p>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach([
                ['01', 'Purchase Reseller Access', 'Choose All Bundles + Reseller Rights and complete your purchase.'],
                ['02', 'Access Digital Products', 'Follow the product delivery instructions to access your included bundles, resources and reseller licence.'],
                ['03', 'Start Selling', 'List eligible products on your permitted sales channels, set your prices and promote your digital products.'],
            ] as [$number, $title, $description])
                <article class="rounded-2xl bg-slate-50 p-5">
                    <span class="text-3xl font-black text-orange-500">{{ $number }}</span>
                    <h4 class="mt-3 font-extrabold text-slate-900">{{ $title }}</h4>
                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <aside class="rounded-3xl border border-amber-200 bg-amber-50 p-6 sm:p-8" aria-labelledby="reseller-licence-title">
        <h3 id="reseller-licence-title" class="text-xl font-black text-slate-900">🛡️ Important Reseller Licence Information</h3>
        <p class="mt-3 text-sm leading-7 text-slate-700"><strong>Reseller Rights Included:</strong> This plan provides reseller permissions for eligible digital products as specified in the applicable licence. Resale, rebranding, customisation, redistribution and bundling permissions are governed by those licence terms. Reseller rights do not automatically grant copyright ownership or unrestricted rights over third-party content. Bundle access and future updates are subject to the purchased plan’s stated inclusions. For personal-use access without reseller permissions, see <a href="{{ route('products.show', 'all-bundles') }}" class="font-bold text-indigo-700 underline">All Bundles — Personal Use</a>.</p>
    </aside>

    <section class="rounded-3xl bg-gradient-to-r from-orange-500 to-rose-500 p-6 text-center text-white shadow-lg sm:p-8">
        <h3 class="text-2xl font-black sm:text-3xl">Ready to Build Your Digital Business? 🚀</h3>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-white/90">Get access to premium digital product bundles with eligible reseller rights. Your brand, your prices and your business journey.</p>
        <button type="button" data-buy-now data-product-id="{{ $product->id }}" class="mt-5 inline-flex items-center gap-3 rounded-xl bg-white px-6 py-3 font-extrabold text-rose-600 shadow transition hover:bg-orange-50">Buy Now — Unlock Reseller Access <span aria-hidden="true">→</span></button>
    </section>
</section>
