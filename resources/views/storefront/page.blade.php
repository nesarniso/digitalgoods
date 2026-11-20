@extends('layouts.storefront')
@section('title', app(\App\Support\SiteBrand::class)->text($page->meta_title ?: $page->title).' | '.config('app.name'))
@section('meta_description', app(\App\Support\SiteBrand::class)->text($page->meta_description ?: Str::limit(strip_tags($page->content), 155)))
@section('content')
@if($page->slug === 'about-us')
<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
    <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-rose-50 px-6 py-10 sm:px-12 sm:py-14">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">Your digital gateway to success</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">About our Digital store</h1>
        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Useful, creative and ready-to-use digital resources for your next idea.</p>
        <a href="{{ route('shop') }}" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-[#f58220] px-6 py-3 font-bold text-white shadow-sm transition hover:bg-orange-600">Explore Products <span aria-hidden="true">→</span></a>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['01', 'Ready to Use', 'Practical digital resources, ready to download and use.'],
            ['02', 'Curated Collections', 'Useful bundles and collections gathered in one place.'],
            ['03', 'Creative Resources', 'Digital assets for creators, marketers and businesses.'],
            ['04', 'Instant Access', 'Get convenient access to your digital products after purchase.'],
        ] as [$number, $title, $description])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-black tracking-widest text-[#f58220]">{{ $number }}</span>
                <h2 class="mt-3 text-lg font-black text-slate-900">{{ $title }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>
            </article>
        @endforeach
    </div>
    <article class="mt-6 rounded-3xl border border-slate-200 bg-white px-6 py-8 shadow-sm sm:px-10 sm:py-10">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">About {{ config('app.name') }}</p>
        <div class="prose prose-slate mt-5 max-w-none break-words leading-8 prose-headings:font-black prose-headings:tracking-tight prose-headings:text-slate-950 prose-h2:mt-10 prose-h2:border-t prose-h2:border-orange-100 prose-h2:pt-7 prose-a:text-orange-700 prose-li:marker:text-orange-500">{!! app(\App\Support\SiteBrand::class)->html($page->content) !!}</div>
    </article>
    <div class="mt-6 rounded-3xl bg-gradient-to-r from-[#fb5360] to-[#f58220] px-6 py-8 text-white sm:flex sm:items-center sm:justify-between sm:px-10">
        <div><p class="text-xs font-black uppercase tracking-[0.2em] text-white/80">Ready to explore?</p><h2 class="mt-2 text-2xl font-black sm:text-3xl">Find Your Next Digital Resource.</h2></div>
        <a href="{{ route('shop') }}" class="mt-5 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 hover:bg-orange-50 sm:mt-0">Explore Products <span class="ml-2">→</span></a>
    </div>
</section>
@elseif($page->slug === 'contact-us')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16">
    @if (session('contact_success'))
        <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('contact_success') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-bold">Please check the highlighted form details.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="mx-auto max-w-3xl py-5 text-center sm:py-8">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['badge'] ?: 'Get in touch') }}</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['hero_title'] ?: "We're Here To Help You.") }}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['hero_text']) }}</p>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        @if(!empty($systemSettings['phone']))
            <a href="tel:{{ $systemSettings['phone'] }}" class="group flex items-center gap-5 rounded-2xl border border-orange-100 bg-gradient-to-br from-white to-orange-50/60 p-5 shadow-sm transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg sm:p-7">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#fb5360] to-[#f58220] text-2xl text-white shadow-md shadow-orange-200">☎</span>
                <span class="min-w-0 flex-1"><span class="block text-[11px] font-black uppercase tracking-[0.16em] text-orange-600">Phone support</span><span class="mt-1 block text-lg font-black text-slate-900">{{ $systemSettings['phone'] }}</span><span class="mt-1 block text-sm leading-5 text-slate-500">Get in touch with our team for quick assistance and support.</span></span><span aria-hidden="true" class="text-lg text-slate-400 group-hover:text-orange-600">↗</span>
            </a>
        @endif
        @if(!empty($systemSettings['email']))
            <a href="mailto:{{ $systemSettings['email'] }}" class="group flex items-center gap-5 rounded-2xl border border-orange-100 bg-gradient-to-br from-white to-orange-50/60 p-5 shadow-sm transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg sm:p-7">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#fb5360] to-[#f58220] text-2xl text-white shadow-md shadow-orange-200">✉</span>
                <span class="min-w-0 flex-1"><span class="block text-[11px] font-black uppercase tracking-[0.16em] text-orange-600">Email support</span><span class="mt-1 block break-all text-lg font-black text-slate-900">{{ $systemSettings['email'] }}</span><span class="mt-1 block text-sm leading-5 text-slate-500">Send us your questions and we'll be happy to assist you.</span></span><span aria-hidden="true" class="text-lg text-slate-400 group-hover:text-orange-600">↗</span>
            </a>
        @endif
    </div>
    @if(!empty($systemSettings['whatsapp']))
        @php
            $whatsappDigits = preg_replace('/\D+/', '', (string) $systemSettings['whatsapp']);
            $whatsappDigits = str_starts_with($whatsappDigits, '0') ? '880'.ltrim($whatsappDigits, '0') : $whatsappDigits;
        @endphp
        <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer" class="group mt-4 flex items-center gap-5 rounded-2xl border border-orange-100 bg-white p-5 shadow-sm transition hover:border-orange-200 hover:shadow-lg sm:p-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-xl text-white">◉</span>
            <span class="min-w-0 flex-1"><span class="block text-[11px] font-black uppercase tracking-[0.16em] text-orange-600">WhatsApp support</span><span class="mt-1 block font-black text-slate-900">{{ $systemSettings['whatsapp'] }}</span><span class="mt-1 block text-sm text-slate-500">Message our team for quick assistance.</span></span>
            <span aria-hidden="true" class="text-lg text-slate-400 group-hover:text-orange-600">↗</span>
        </a>
    @endif
    <section class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="grid lg:grid-cols-[0.9fr_1.1fr]">
            <div class="flex flex-col justify-center bg-gradient-to-br from-orange-50 via-white to-rose-50 p-7 sm:p-10">
                @if(!empty($systemSettings['logo_url']))<img src="{{ $systemSettings['logo_url'] }}" alt="{{ $systemSettings['site_name'] ?? config('app.name') }}" class="mb-8 h-12 max-w-48 object-contain object-left">@else<p class="mb-8 text-xl font-black text-[#f58220]">{{ $systemSettings['site_name'] ?? config('app.name') }}</p>@endif
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">{{ config('app.name') }} support</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Need a little help?</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['form_text']) }}</p>
                @if(!empty($systemSettings['address']))<p class="mt-6 border-t border-orange-100 pt-5 text-sm leading-6 text-slate-600"><strong class="block text-slate-900">Our location</strong>{{ $systemSettings['address'] }} <a class="mt-1 inline-block font-bold text-orange-700 hover:underline" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($systemSettings['address']) }}" target="_blank" rel="noopener noreferrer">Get directions →</a></p>@endif
            </div>
            <div class="grid sm:grid-cols-2">
                @foreach([
                    ['Quick Assistance', 'Get help with your questions and concerns.', '⚡'],
                    ['Download Support', 'Having trouble accessing your digital product?', '↓'],
                    ['Product Assistance', 'Questions about any of our digital products.', '▣'],
                    ['Customer Support', 'Our team is ready to assist you.', '◉'],
                ] as [$title, $description, $icon])
                    <div class="border-t border-slate-100 p-6 sm:border-l sm:p-8"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 font-black text-orange-600">{{ $icon }}</span><h3 class="mt-4 font-black text-slate-900">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p></div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="mt-6 rounded-3xl bg-gradient-to-r from-[#fb5360] to-[#f58220] p-6 text-white shadow-sm sm:flex sm:items-center sm:justify-between sm:px-9 sm:py-7">
        <div><p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/80">Have a question?</p><h2 class="mt-2 text-2xl font-black">Let's talk.</h2><p class="mt-1 text-sm text-white/90">Choose your preferred way to contact us.</p></div>
        <div class="mt-5 flex flex-wrap gap-3 sm:mt-0">@if(!empty($systemSettings['email']))<a href="mailto:{{ $systemSettings['email'] }}" class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-orange-700 transition hover:bg-orange-50">✉ &nbsp; Email Us</a>@endif @if(!empty($systemSettings['phone']))<a href="tel:{{ $systemSettings['phone'] }}" class="rounded-xl border border-white/60 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">☎ &nbsp; Call Us</a>@endif</div>
    </section>
    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1.15fr_0.85fr]">
        <form method="POST" action="{{ route('pages.contact') }}" class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-10">
            @csrf
            <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['form_eyebrow']) }}</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['form_title']) }}</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['form_text']) }}</p>
            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                <div><label for="contact-name" class="text-sm font-bold text-slate-700">Your name <span class="text-red-500">*</span></label><input id="contact-name" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></div>
                <div><label for="contact-email" class="text-sm font-bold text-slate-700">Email address <span class="text-red-500">*</span></label><input id="contact-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></div>
                <div><label for="contact-phone" class="text-sm font-bold text-slate-700">Phone number</label><input id="contact-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></div>
                <div><label for="contact-topic" class="text-sm font-bold text-slate-700">What do you need help with? <span class="text-red-500">*</span></label><select id="contact-topic" name="topic" required class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"><option value="">Select a topic</option>@foreach(['Order support','Product question','Payment','Returns and refunds','General inquiry'] as $topic)<option value="{{ $topic }}" @selected(old('topic') === $topic)>{{ $topic }}</option>@endforeach</select></div>
                <div class="sm:col-span-2"><label for="contact-order" class="text-sm font-bold text-slate-700">Order number <span class="font-normal text-slate-400">(optional)</span></label><input id="contact-order" name="order_number" value="{{ old('order_number') }}" placeholder="For order, delivery or return questions" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></div>
                <div class="sm:col-span-2"><label for="contact-message" class="text-sm font-bold text-slate-700">Message <span class="text-red-500">*</span></label><textarea id="contact-message" name="message" rows="5" required minlength="10" maxlength="5000" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('message') }}</textarea></div>
            </div>
            <p class="mt-4 text-xs leading-5 text-slate-500">By sending this form, you agree that we may use your details to respond to your request.</p>
            <button type="submit" class="mt-6 rounded-xl bg-[var(--brand-primary)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:opacity-90">Send message <span class="ml-2">→</span></button>
        </form>
        <aside class="space-y-6">
            <div class="rounded-3xl bg-slate-950 p-6 text-white sm:p-8">
            <span class="text-3xl text-indigo-300">✦</span>
            <h2 class="mt-5 text-2xl font-black">Before you contact us</h2>
            <ul class="mt-6 space-y-4 text-sm leading-6 text-slate-300">
                <li class="flex gap-3"><span class="text-indigo-300">✓</span><span>Keep your order number ready for order-related help.</span></li>
                <li class="flex gap-3"><span class="text-indigo-300">✓</span><span>Tell us the product name and what you need.</span></li>
                <li class="flex gap-3"><span class="text-indigo-300">✓</span><span>Our team will review your request and respond clearly.</span></li>
            </ul>
                <a href="{{ route('shop') }}" class="mt-8 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-950 hover:bg-indigo-50">Continue shopping <span class="ml-2">→</span></a>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Support details</p>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <p><strong class="block text-slate-900">Support hours</strong>{{ app(\App\Support\SiteBrand::class)->text($contactSettings['support_hours']) }}</p>
                    <p><strong class="block text-slate-900">Response time</strong>{{ app(\App\Support\SiteBrand::class)->text($contactSettings['response_time']) }}</p>
                    @if (!empty($systemSettings['address']))<p><strong class="block text-slate-900">Our location</strong>{{ $systemSettings['address'] }} <a class="mt-1 inline-block font-bold text-[var(--brand-primary)] hover:underline" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($systemSettings['address']) }}" target="_blank" rel="noopener">Get directions →</a></p>@endif
                </div>
            </div>
        </aside>
    </div>
    <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
        <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['faq_eyebrow']) }}</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ app(\App\Support\SiteBrand::class)->text($contactSettings['faq_title']) }}</h2>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach($contactSettings['faq_items'] as $faq)
                <details class="group rounded-2xl border border-slate-200 p-5"><summary class="cursor-pointer list-none pr-6 font-bold text-slate-900">{{ app(\App\Support\SiteBrand::class)->text($faq['question']) }} <span class="float-right text-[var(--brand-primary)] group-open:rotate-45">+</span></summary><p class="mt-3 text-sm leading-6 text-slate-500">{{ app(\App\Support\SiteBrand::class)->text($faq['answer']) }}</p></details>
            @endforeach
        </div>
    </div>
</section>
@else
@php
    $policySlugs = ['privacy-policy', 'terms-and-conditions', 'terms-conditions', 'shipping-and-delivery', 'shipping-delivery', 'returns-refunds', 'returns-and-refunds', 'return-and-refund', 'faq', 'frequently-asked-questions'];
    $isPolicyPage = in_array($page->slug, $policySlugs, true);
    $isRefundPage = in_array($page->slug, ['returns-refunds', 'returns-and-refunds', 'return-and-refund'], true);
    $contactUrl = $isRefundPage ? route('contact-us') : route('contact');
@endphp
@if(in_array($page->slug, ['faq', 'frequently-asked-questions'], true))
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16">
    <div class="max-w-3xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-[var(--brand-primary)]">Quick answers</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">{{ app(\App\Support\SiteBrand::class)->text($page->title) }}</h1>
        <p class="mt-5 text-base leading-8 text-slate-500">Find clear answers to the questions our customers ask most.</p>
    </div>
    <div class="mt-10 grid gap-5 md:grid-cols-2">
        @foreach($faqItems as $faq)
            <details class="group min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-[var(--brand-primary)] hover:shadow-md sm:p-6">
            <summary class="flex cursor-pointer list-none items-start justify-between gap-3 break-words text-base font-bold text-slate-950 sm:text-lg">{{ app(\App\Support\SiteBrand::class)->text($faq['question'] ?? '') }}<span class="shrink-0 text-xl text-[var(--brand-primary)] transition group-open:rotate-45">+</span></summary>
                <p class="mt-4 border-t border-slate-100 pt-4 text-sm leading-7 text-slate-500">{{ app(\App\Support\SiteBrand::class)->text($faq['answer'] ?? '') }}</p>
            </details>
        @endforeach
    </div>
</section>
@else
<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
    <header class="overflow-hidden rounded-[2rem] border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-rose-50 px-6 py-9 sm:px-10 sm:py-11">
        <nav class="text-sm font-semibold text-slate-500" aria-label="Breadcrumb"><a href="{{ route('home') }}" class="hover:text-orange-600">Home</a><span class="mx-2">/</span><span class="text-slate-800">{{ app(\App\Support\SiteBrand::class)->text($page->title) }}</span></nav>
        <p class="mt-6 text-xs font-black uppercase tracking-[0.2em] text-orange-600">{{ $isRefundPage ? 'Customer care · Clear and helpful' : ($isPolicyPage ? 'Customer information' : config('app.name')) }}</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">{{ app(\App\Support\SiteBrand::class)->text($page->title) }}</h1>
        @if($isRefundPage)<p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Please review our digital product refund and returns policy before placing an order.</p>@endif
    </header>
    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_260px]">
        <article class="min-w-0 rounded-3xl border border-slate-200 bg-white px-6 py-7 shadow-sm sm:px-10 sm:py-10">
            <div class="prose prose-slate max-w-none break-words leading-8 prose-headings:mt-8 prose-headings:font-black prose-headings:tracking-tight prose-headings:text-slate-900 prose-h2:border-t prose-h2:border-orange-100 prose-h2:pt-6 prose-a:text-orange-700 prose-strong:text-slate-900 prose-li:marker:text-orange-500">{!! app(\App\Support\SiteBrand::class)->html($page->content) !!}</div>
        </article>
        <aside class="space-y-4 lg:sticky lg:top-24">
            @if($isRefundPage && !empty($systemSettings['email']))
                <a href="mailto:{{ $systemSettings['email'] }}" class="block rounded-2xl border border-orange-100 bg-white p-5 shadow-sm transition hover:border-orange-200 hover:shadow-md"><p class="text-xs font-black uppercase tracking-widest text-orange-600">Email support</p><p class="mt-2 break-all font-black text-slate-900">{{ $systemSettings['email'] }}</p></a>
            @endif
            @if($isRefundPage && !empty($systemSettings['phone']))
                <a href="tel:{{ $systemSettings['phone'] }}" class="block rounded-2xl border border-orange-100 bg-white p-5 shadow-sm transition hover:border-orange-200 hover:shadow-md"><p class="text-xs font-black uppercase tracking-widest text-orange-600">Phone support</p><p class="mt-2 font-black text-slate-900">{{ $systemSettings['phone'] }}</p></a>
            @endif
            <div class="rounded-2xl bg-indigo-50 p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-700">Need help?</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">If anything is unclear, our support team is ready to help.</p>
                <a href="{{ $contactUrl }}" class="mt-4 inline-flex rounded-xl bg-[#f58220] px-4 py-2.5 text-sm font-bold text-white hover:bg-orange-600">Contact us →</a>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                <p class="font-bold text-slate-900">Helpful links</p>
                <div class="mt-3 space-y-2">
                    <a href="{{ route('shop') }}" class="block hover:text-indigo-700">Browse products</a>
                    <a href="{{ $contactUrl }}" class="block hover:text-orange-700">Contact support</a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endif
@endif
@endsection
