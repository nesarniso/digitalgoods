<section id="complete-plan-details" class="scroll-mt-24 space-y-6" aria-labelledby="personal-plan-details-title">
    <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-rose-950 p-6 text-white shadow-xl sm:p-10">
        <div class="absolute -right-12 -top-20 h-56 w-56 rounded-full bg-rose-500/20 blur-2xl" aria-hidden="true"></div>
        <div class="relative">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-orange-300">✦ {{ Str::upper($siteBrand->text('Premium Digital Bundle Access')) }}</p>
            <h2 id="personal-plan-details-title" class="mt-4 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl">Everything You Need to Create More</h2>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-200 sm:text-base">Unlock ready-made reels, premium digital content, video assets and creative resources in one convenient collection. Save time, simplify your workflow and focus on growing your content.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach(['Ready-Made Reels', 'Premium Creative Assets', 'HD Content', 'Personal Use Access'] as $tag)
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-white">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </header>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="personal-plan-features-title">
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">Everything you get</p>
        <h3 id="personal-plan-features-title" class="mt-2 text-2xl font-black text-slate-900">Powerful Features, One Plan ✨</h3>
        <p class="mt-2 text-sm text-slate-500">Discover digital content and creative resources designed to make content creation simpler.</p>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['🎬', 'Ready-to-Post Reels & Shorts', 'Access a wide collection of ready-made reels, short videos and content assets. Save time on content preparation.'],
                ['💎', 'HD Quality Content', 'Enjoy high-quality digital content with clear visuals for your creative and social media projects.'],
                ['✨', 'Watermark-Free Files', 'Use included watermark-free content to maintain a clean, professional appearance in your projects.'],
                ['📂', 'Multiple Content Niches', 'Explore AI reels, entertainment, educational content, animation, devotional content and more.'],
                ['🎨', 'Creative Assets & Templates', 'Explore design resources, graphics and editable templates available in the included bundles.'],
                ['🌐', 'Hindi & English Content', 'Access a diverse collection of content suitable for different audience preferences and languages.'],
                ['⬇️', 'Organised & Easy Downloads', 'Conveniently organised digital resources help you find and download content with ease.'],
                ['📱', 'Mobile & Desktop Access', 'Access your compatible digital files using supported mobile and desktop devices.'],
                ['🔄', 'Eligible Bundle Updates', 'Access applicable bundle updates included under your purchased plan and access terms.'],
                ['🤝', 'Download Assistance', 'Get assistance with product downloads and access-related questions.'],
            ] as [$icon, $title, $description])
                <article class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                    <h4 class="mt-3 font-extrabold text-slate-900">{{ $title }}</h4>
                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-orange-100 bg-orange-50/60 p-6 sm:p-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">A simpler creative workflow</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">Why Choose This Plan?</h3>
            <ul class="mt-5 space-y-3">
                @foreach(['Save time on content preparation', 'Explore multiple creative categories', 'Access ready-made digital content', 'Simplify your creative workflow', 'Find resources in one collection', 'Create content more efficiently'] as $benefit)
                    <li class="flex items-start gap-3 text-sm font-medium text-slate-700"><span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-black text-emerald-700">✓</span>{{ $benefit }}</li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-6 sm:p-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-600">Made for creators</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">Who Is This Plan For?</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">A convenient resource collection for individuals and creative professionals.</p>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach(['🎥 Content Creators', '📱 Social Media Creators', '🎨 Graphic Designers', '✂️ Video Editors', '📣 Digital Marketers', '⭐ Influencers'] as $audience)
                    <span class="rounded-full border border-indigo-100 bg-white px-3 py-2 text-xs font-bold text-slate-700">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="personal-plan-steps-title">
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">Simple &amp; convenient</p>
        <h3 id="personal-plan-steps-title" class="mt-2 text-2xl font-black text-slate-900">How It Works</h3>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach([
                ['01', 'Choose Your Plan', 'Select All Bundles — Personal Use and complete your purchase.'],
                ['02', 'Access Your Bundles', 'Follow the provided delivery instructions to access your included digital resources.'],
                ['03', 'Download & Create', 'Download compatible files and start working on your personal content projects.'],
            ] as [$number, $title, $description])
                <article class="rounded-2xl bg-slate-50 p-5">
                    <span class="text-3xl font-black text-orange-500">{{ $number }}</span>
                    <h4 class="mt-3 font-extrabold text-slate-900">{{ $title }}</h4>
                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <aside class="rounded-3xl border border-amber-200 bg-amber-50 p-6 sm:p-8" aria-labelledby="personal-plan-licence-title">
        <h3 id="personal-plan-licence-title" class="text-xl font-black text-slate-900">🛡️ Important Licence Information</h3>
        <p class="mt-3 text-sm leading-7 text-slate-700"><strong>Personal Use Licence:</strong> This plan provides access to eligible digital bundles for permitted personal content-creation purposes. Reselling, redistributing or transferring the original digital products is not allowed. For product resale permissions, choose the separate <a href="{{ route('products.show', 'all-bundles-reseller-rights') }}" class="font-bold text-indigo-700 underline">All Bundles + Reseller Rights</a> plan. Bundle inclusion, usage permissions and eligible updates are governed by the applicable product and licence terms.</p>
    </aside>

    <section class="rounded-3xl bg-gradient-to-r from-orange-500 to-rose-500 p-6 text-center text-white shadow-lg sm:p-8">
        <h3 class="text-2xl font-black sm:text-3xl">Ready to Create Something Amazing? 🚀</h3>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-white/90">Get access to premium digital content, ready-made reels and creative assets with All Bundles — Personal Use.</p>
        <button type="button" data-buy-now data-product-id="{{ $product->id }}" class="mt-5 inline-flex items-center gap-3 rounded-xl bg-white px-6 py-3 font-extrabold text-rose-600 shadow transition hover:bg-orange-50">Buy Now — Get Personal Access <span aria-hidden="true">→</span></button>
    </section>
</section>
