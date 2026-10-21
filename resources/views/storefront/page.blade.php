@extends('layouts.storefront')
@section('title', ($page->meta_title ?: $page->title).' | '.config('app.name'))
@section('meta_description', $page->meta_description ?: Str::limit(strip_tags($page->content), 155))
@section('content')
@if($page->slug === 'about-us')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16">
    <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-12 text-white shadow-2xl shadow-indigo-200/40 sm:px-12 sm:py-16">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div>
        <div class="absolute -bottom-32 left-1/3 h-72 w-72 rounded-full bg-violet-500/20 blur-3xl"></div>
        <div class="relative max-w-3xl">
            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-indigo-200">About {{ $systemSettings['site_name'] ?? config('app.name') }}</span>
            <h1 class="mt-6 text-4xl font-black tracking-tight sm:text-6xl">Digital products, delivered with confidence.</h1>
            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">A trusted destination for useful digital products, clear service and a shopping experience designed around you.</p>
            <a href="{{ route('shop') }}" class="mt-8 inline-flex rounded-xl bg-white px-6 py-3 font-bold text-slate-950 transition hover:-translate-y-0.5 hover:bg-indigo-50">Explore our products <span class="ml-2">→</span></a>
        </div>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><span class="text-2xl text-[var(--brand-primary)]">✦</span><p class="mt-4 text-lg font-black">Curated quality</p><p class="mt-2 text-sm leading-6 text-slate-500">Products selected to bring real value to everyday life.</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><span class="text-2xl text-[var(--brand-primary)]">⌁</span><p class="mt-4 text-lg font-black">Simple experience</p><p class="mt-2 text-sm leading-6 text-slate-500">Clear pricing, smooth checkout and helpful support.</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><span class="text-2xl text-[var(--brand-primary)]">♡</span><p class="mt-4 text-lg font-black">Customer first</p><p class="mt-2 text-sm leading-6 text-slate-500">We are here to make every order feel dependable.</p></div>
    </div>
    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <article class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-10">
            <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">Our story</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Built for better digital shopping</h2>
            <div class="prose prose-slate mt-6 max-w-none break-words leading-7">{!! $page->content !!}</div>
        </article>
        <aside class="rounded-3xl bg-indigo-50 p-6 sm:p-8">
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-700">Our promise</p>
            <h2 class="mt-3 text-2xl font-black text-slate-900">Reliable from discovery to delivery.</h2>
            <ul class="mt-6 space-y-4 text-sm leading-6 text-slate-600">
                <li class="flex gap-3"><span class="font-black text-indigo-600">01</span><span>Transparent product information and pricing.</span></li>
                <li class="flex gap-3"><span class="font-black text-indigo-600">02</span><span>Secure ordering with responsive customer care.</span></li>
                <li class="flex gap-3"><span class="font-black text-indigo-600">03</span><span>Support that stays with you after purchase.</span></li>
            </ul>
        </aside>
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
    <div class="relative overflow-hidden rounded-[2rem] bg-indigo-600 px-6 py-12 text-white shadow-2xl shadow-indigo-200/50 sm:px-12 sm:py-16">
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/15 blur-3xl"></div>
        <div class="relative max-w-3xl">
            <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-indigo-100">{{ $contactSettings['badge'] }}</span>
            <h1 class="mt-6 text-4xl font-black tracking-tight sm:text-6xl">{{ $contactSettings['hero_title'] }}</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-indigo-100 sm:text-lg">{{ $contactSettings['hero_text'] }}</p>
        </div>
    </div>
    <div class="mt-8 grid gap-5 md:grid-cols-3">
        @if(!empty($systemSettings['phone']))
            <a href="tel:{{ $systemSettings['phone'] }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-xl text-indigo-600">☎</span>
                <p class="mt-5 text-xs font-bold uppercase tracking-widest text-slate-400">Call us</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ $systemSettings['phone'] }}</p>
                <p class="mt-2 text-sm text-slate-500">For quick order support</p>
            </a>
        @endif
        @if(!empty($systemSettings['email']))
            <a href="mailto:{{ $systemSettings['email'] }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-xl text-indigo-600">✉</span>
                <p class="mt-5 text-xs font-bold uppercase tracking-widest text-slate-400">Email us</p>
                <p class="mt-2 break-all text-lg font-black text-slate-900">{{ $systemSettings['email'] }}</p>
                <p class="mt-2 text-sm text-slate-500">For detailed questions</p>
            </a>
        @endif
    </div>
    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1.15fr_0.85fr]">
        <form method="POST" action="{{ route('pages.contact') }}" class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-10">
            @csrf
            <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">{{ $contactSettings['form_eyebrow'] }}</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ $contactSettings['form_title'] }}</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">{{ $contactSettings['form_text'] }}</p>
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
                    <p><strong class="block text-slate-900">Support hours</strong>{{ $contactSettings['support_hours'] }}</p>
                    <p><strong class="block text-slate-900">Response time</strong>{{ $contactSettings['response_time'] }}</p>
                    @if (!empty($systemSettings['address']))<p><strong class="block text-slate-900">Our location</strong>{{ $systemSettings['address'] }} <a class="mt-1 inline-block font-bold text-[var(--brand-primary)] hover:underline" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($systemSettings['address']) }}" target="_blank" rel="noopener">Get directions →</a></p>@endif
                </div>
            </div>
        </aside>
    </div>
    <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
        <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">{{ $contactSettings['faq_eyebrow'] }}</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ $contactSettings['faq_title'] }}</h2>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach($contactSettings['faq_items'] as $faq)
                <details class="group rounded-2xl border border-slate-200 p-5"><summary class="cursor-pointer list-none pr-6 font-bold text-slate-900">{{ $faq['question'] }} <span class="float-right text-[var(--brand-primary)] group-open:rotate-45">+</span></summary><p class="mt-3 text-sm leading-6 text-slate-500">{{ $faq['answer'] }}</p></details>
            @endforeach
        </div>
    </div>
</section>
@else
@php
    $policySlugs = ['privacy-policy', 'terms-and-conditions', 'terms-conditions', 'shipping-and-delivery', 'shipping-delivery', 'returns-and-refunds', 'return-and-refund', 'faq', 'frequently-asked-questions'];
    $isPolicyPage = in_array($page->slug, $policySlugs, true);
@endphp
@if(in_array($page->slug, ['faq', 'frequently-asked-questions'], true))
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16">
    <div class="max-w-3xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-[var(--brand-primary)]">Quick answers</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">{{ $page->title }}</h1>
        <p class="mt-5 text-base leading-8 text-slate-500">Find clear answers to the questions our customers ask most.</p>
    </div>
    <div class="mt-10 grid gap-5 md:grid-cols-2">
        @foreach($faqItems as $faq)
            <details class="group min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-[var(--brand-primary)] hover:shadow-md sm:p-6">
            <summary class="flex cursor-pointer list-none items-start justify-between gap-3 break-words text-base font-bold text-slate-950 sm:text-lg">{{ $faq['question'] ?? '' }}<span class="shrink-0 text-xl text-[var(--brand-primary)] transition group-open:rotate-45">+</span></summary>
                <p class="mt-4 border-t border-slate-100 pt-4 text-sm leading-7 text-slate-500">{{ $faq['answer'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</section>
@else
<section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14">
    <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-10 text-white shadow-2xl sm:px-12 sm:py-14">
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-indigo-500/25 blur-3xl"></div>
        <div class="relative max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-indigo-300">{{ $isPolicyPage ? 'Customer information' : config('app.name') }}</p>
            <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-6xl">{{ $page->title }}</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300">Clear information about our policies, services and customer support.</p>
        </div>
    </div>
    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_260px]">
        <article class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-10">
            <div class="prose prose-slate max-w-none break-words leading-8">{!! $page->content !!}</div>
        </article>
        <aside class="space-y-4">
            <div class="rounded-2xl bg-indigo-50 p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-700">Need help?</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">If anything is unclear, our support team is ready to help.</p>
                <a href="{{ route('contact') }}" class="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Contact us →</a>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                <p class="font-bold text-slate-900">Helpful links</p>
                <div class="mt-3 space-y-2">
                    <a href="{{ route('shop') }}" class="block hover:text-indigo-700">Browse products</a>
                    <a href="{{ route('contact') }}" class="block hover:text-indigo-700">Contact support</a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endif
@endif
@endsection
