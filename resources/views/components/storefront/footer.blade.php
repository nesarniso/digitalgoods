@if ($footerSettings['enabled'] ?? true)
    <footer class="mt-20 bg-[#0f172a] text-slate-300">
        <div class="mx-auto max-w-6xl px-6 py-12">
            @php
                $customerCareLinks = [];
                $quickLinks = $footerSettings['column_one_links'];
                $moveToQuickLinks = false;
                $allCustomerCareLinks = $footerSettings['column_two_links'];
                foreach ($footerPages as $page) {
                    $allCustomerCareLinks[] = [
                        'label' => $page->title,
                        'url' => match ($page->slug) {
                            'about-us' => route('about-us'),
                            'contact-us' => route('contact-us'),
                            'returns-refunds' => route('refund-returns'),
                            default => route('pages.show', $page),
                        },
                    ];
                }
                foreach ($allCustomerCareLinks as $link) {
                    if ($moveToQuickLinks) {
                        $quickLinks[] = $link;
                    } else {
                        $customerCareLinks[] = $link;
                        $moveToQuickLinks = str_contains(strtolower($link['label'] ?? ''), 'privacy');
                    }
                }
            @endphp
            <div class="storefront-footer-grid grid grid-cols-1 gap-10">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center">
                        @if (!empty($systemSettings['logo_url']))
                            <img src="{{ $systemSettings['logo_url'] }}" alt="{{ $systemSettings['site_name'] ?? config('app.name') }}" class="h-10 max-w-[170px] object-contain">
                        @else
                            <span class="text-xl font-black text-white">{{ $systemSettings['site_name'] ?? config('app.name') }}</span>
                        @endif
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-6 text-slate-300">{{ app(\App\Support\SiteBrand::class)->text($footerSettings['description']) }}</p>
                    <div class="mt-4 space-y-2 text-sm font-medium text-slate-200">
                        @if (!empty($systemSettings['phone']))
                            <a href="tel:{{ $systemSettings['phone'] }}" class="flex items-center gap-2 transition hover:text-orange-300">
                                <span aria-hidden="true" class="text-orange-300">☎</span><span>{{ $systemSettings['phone'] }}</span>
                            </a>
                        @endif
                        @if (!empty($systemSettings['email']))
                            <a href="mailto:{{ $systemSettings['email'] }}" class="flex items-center gap-2 transition hover:text-orange-300">
                                <span aria-hidden="true" class="text-orange-300">✉</span><span>{{ $systemSettings['email'] }}</span>
                            </a>
                        @endif
                        @if (!empty($systemSettings['address']))
                            <span class="flex items-center gap-2 text-slate-300">
                                <span aria-hidden="true" class="text-orange-300">⌖</span><span>{{ $systemSettings['address'] }}</span>
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <h2 class="font-bold text-white">{{ $footerSettings['column_one_title'] }}</h2>
                    <ul class="mt-4 space-y-3 text-sm text-slate-300">
                        @foreach($quickLinks as $link)
                            <li><a href="{{ $link['url'] }}" class="transition hover:text-orange-300">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2 class="font-bold text-white">{{ $footerSettings['column_two_title'] }}</h2>
                    <ul class="mt-4 space-y-3 text-sm text-slate-300">
                        @foreach($customerCareLinks as $link)
                            <li><a href="{{ $link['url'] }}" class="transition hover:text-orange-300">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2 class="font-bold text-white">Follow us</h2>
                    <p class="mt-4 text-sm leading-6 text-slate-300">Connect with us on social media for updates, products and offers.</p>
                    <div class="mt-5">
                        <div class="flex flex-wrap gap-2">
                            @foreach(['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube'] as $key => $label)
                                @if (!empty($footerSettings[$key]))
                                    <a href="{{ $footerSettings[$key] }}" target="_blank" rel="noopener" class="rounded-full border border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:border-orange-300 hover:text-orange-300">{{ $label }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-10 flex flex-wrap justify-between gap-3 border-t border-slate-800 pt-5 text-xs text-slate-500">
                <span>{{ app(\App\Support\SiteBrand::class)->text($footerSettings['copyright']) }}</span>
            </div>
        </div>
    </footer>
@endif
