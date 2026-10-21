@extends('layouts.admin')
@section('title', 'SEO & sitemap')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Search visibility</p>
        <h1 class="mt-2 text-3xl font-black">SEO & sitemap</h1>
        <p class="mt-2 max-w-2xl text-slate-500">Use these public endpoints with Google Search Console, Bing Webmaster Tools, and social preview checks.</p>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">XML sitemap</p>
            <h2 class="mt-2 text-xl font-black">Published URLs</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Automatically includes the storefront, shop, and all published products.</p>
            <a href="{{ $sitemapUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">Open sitemap</a>
            <code class="mt-4 block break-all rounded-lg bg-slate-50 p-3 text-xs text-slate-600">{{ $sitemapUrl }}</code>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Crawler rules</p>
            <h2 class="mt-2 text-xl font-black">robots.txt</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Protects account, cart, checkout, and admin pages while pointing crawlers to the sitemap.</p>
            <a href="{{ $robotsUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">Open robots.txt</a>
            <code class="mt-4 block break-all rounded-lg bg-slate-50 p-3 text-xs text-slate-600">{{ $robotsUrl }}</code>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Storefront checks</p>
            <h2 class="mt-2 text-xl font-black">SEO checklist</h2>
            <ul class="mt-4 space-y-3 text-sm text-slate-600">
                <li>✓ Product pages use canonical URLs.</li>
                <li>✓ Product pages publish JSON-LD structured data.</li>
                <li>✓ Published products are included in the sitemap.</li>
                <li>✓ Private account and checkout pages are blocked.</li>
            </ul>
            <a href="{{ $storefrontUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700">View storefront</a>
        </div>
    </div>
</section>
@endsection
