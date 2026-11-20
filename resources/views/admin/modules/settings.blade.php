@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">{{ ($isHeroSection ?? false) ? 'Hero customization' : (($isHomePageSection ?? false) ? 'Home page' : 'Settings') }}</h1>
            <p class="mt-2 text-slate-500">{{ ($isHeroSection ?? false) ? 'Create a clear, attractive homepage hero with your own message, images and action buttons.' : (($isHomePageSection ?? false) ? 'Turn homepage sections on or off and edit their visible text.' : 'Manage global website identity, contact details, branding and homepage sections.') }}</p>
        </div>
    </div>
    @if(session('success'))
        <div class="mt-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700" role="status">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700" role="alert">{{ session('error') }}</div>
    @endif

    @php $isHeroSection = $isHeroSection ?? false; @endphp
    @if($isHomePageSection ?? false)
    <form method="POST" action="{{ route('admin.settings.homepage') }}" class="mt-8 space-y-6">
        @csrf
        <input type="hidden" name="settings_scope" value="home">
        @php
            $sections = [
                'stats_section_enabled' => 'Statistics',
                'entrepreneur_section_enabled' => 'Entrepreneur call-to-action',
                'category_section_enabled' => 'Categories',
                'most_selling_section_enabled' => 'Most selling products',
                'products_section_enabled' => 'Latest and featured products',
                'benefits_section_enabled' => 'Benefits',
                'why_section_enabled' => 'Why eflix',
                'sell_section_enabled' => 'Online and offline selling',
                'how_section_enabled' => 'How to start',
                'cta_section_enabled' => 'Bottom call-to-action',
            ];
        @endphp
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Homepage sections</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($sections as $key => $label)
                    <label class="flex items-center justify-between rounded-xl border border-slate-200 p-3 font-medium">
                        {{ $label }}
                        <input type="checkbox" name="{{ $key }}" value="1" @checked($homepageSettings[$key]) class="h-4 w-4 rounded text-indigo-600">
                    </label>
                @endforeach
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Section text</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <input name="category_eyebrow" value="{{ $homepageSettings['category_eyebrow'] }}" placeholder="Category eyebrow" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="category_title" value="{{ $homepageSettings['category_title'] }}" placeholder="Category title" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="most_selling_eyebrow" value="{{ $homepageSettings['most_selling_eyebrow'] }}" placeholder="Most selling eyebrow" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="most_selling_title" value="{{ $homepageSettings['most_selling_title'] }}" placeholder="Most selling title" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="products_eyebrow" value="{{ $homepageSettings['products_eyebrow'] }}" placeholder="Products eyebrow" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="products_title" value="{{ $homepageSettings['products_title'] }}" placeholder="Products title" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="entrepreneur_eyebrow" value="{{ $homepageSettings['entrepreneur_eyebrow'] }}" placeholder="Entrepreneur eyebrow" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="entrepreneur_title" value="{{ $homepageSettings['entrepreneur_title'] }}" placeholder="Entrepreneur title" class="rounded-xl border border-slate-300 px-3 py-2">
                <textarea name="entrepreneur_text" rows="3" placeholder="Entrepreneur description" class="rounded-xl border border-slate-300 px-3 py-2 sm:col-span-2">{{ $homepageSettings['entrepreneur_text'] }}</textarea>
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Statistics and benefits text</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach($homepageSettings['stats_labels'] as $index => $label)
                    <input name="stats_labels[{{ $index }}]" value="{{ $label }}" placeholder="Statistic label {{ $index + 1 }}" class="rounded-xl border border-slate-300 px-3 py-2">
                @endforeach
                @foreach($homepageSettings['benefit_titles'] as $index => $label)
                    <div class="rounded-xl border border-slate-200 p-3">
                        <input name="benefit_titles[{{ $index }}]" value="{{ $label }}" placeholder="Benefit title {{ $index + 1 }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                        <input name="benefit_texts[{{ $index }}]" value="{{ $homepageSettings['benefit_texts'][$index] }}" placeholder="Benefit description" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                @endforeach
            </div>
        </div>
        @foreach([
            ['field' => 'entrepreneur_steps', 'title' => 'Build your business with eflix'],
            ['field' => 'why_items', 'title' => 'Why eflix'],
            ['field' => 'faq_items', 'title' => 'FAQ'],
            ['field' => 'sell_items', 'title' => 'Online and offline selling'],
            ['field' => 'how_steps', 'title' => 'How to start'],
        ] as $section)
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-xl font-black">{{ $section['title'] }}</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2" @if($section['field'] === 'faq_items') data-faq-list @endif>
                    @foreach($homepageSettings[$section['field']] as $index => $item)
                        <div class="rounded-xl border border-slate-200 p-3" @if($section['field'] === 'faq_items') data-faq-item @endif>
                            <input name="{{ $section['field'] }}[{{ $index }}][title]" value="{{ $item['title'] }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Title">
                            <textarea name="{{ $section['field'] }}[{{ $index }}][text]" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Description">{{ $item['text'] }}</textarea>
                            @if($section['field'] === 'faq_items')
                                <button type="button" data-remove-faq class="mt-2 rounded-lg px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50">Delete FAQ</button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($section['field'] === 'why_items')
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <input name="why_eyebrow" value="{{ $homepageSettings['why_eyebrow'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="Why eflix eyebrow">
                        <input name="why_title" value="{{ $homepageSettings['why_title'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="Why eflix title">
                    </div>
                @elseif($section['field'] === 'faq_items')
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <input name="faq_eyebrow" value="{{ $homepageSettings['faq_eyebrow'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="FAQ eyebrow">
                        <input name="faq_title" value="{{ $homepageSettings['faq_title'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="FAQ title">
                    </div>
                    <button type="button" data-add-faq class="mt-4 rounded-lg border border-indigo-200 px-3 py-2 text-sm font-bold text-indigo-700 hover:bg-indigo-50">+ Add new FAQ</button>
                @elseif($section['field'] === 'sell_items')
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <input name="sell_eyebrow" value="{{ $homepageSettings['sell_eyebrow'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="Section eyebrow">
                        <input name="sell_title" value="{{ $homepageSettings['sell_title'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="Section title">
                        <textarea name="sell_text" rows="2" class="rounded-xl border border-slate-300 px-3 py-2 sm:col-span-2" placeholder="Section description">{{ $homepageSettings['sell_text'] }}</textarea>
                        <input name="sell_drop_title" value="{{ $homepageSettings['sell_drop_title'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="Dropshipping title">
                        <textarea name="sell_drop_text" rows="2" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="Dropshipping description">{{ $homepageSettings['sell_drop_text'] }}</textarea>
                    </div>
                @elseif($section['field'] === 'how_steps')
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <input name="how_eyebrow" value="{{ $homepageSettings['how_eyebrow'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="How to start eyebrow">
                        <input name="how_title" value="{{ $homepageSettings['how_title'] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="How to start title">
                    </div>
                @endif
            </div>
        @endforeach
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Bottom call-to-action</h2>
            <label class="mt-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="cta_section_enabled" value="1" @checked($homepageSettings['cta_section_enabled']) class="h-4 w-4 rounded text-indigo-600">
                Show this section on the homepage
            </label>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach(['cta_eyebrow', 'cta_title', 'cta_button'] as $field)
                    <input name="{{ $field }}" value="{{ $homepageSettings[$field] }}" class="rounded-xl border border-slate-300 px-3 py-2" placeholder="{{ Str::headline(str_replace('_', ' ', $field)) }}">
                @endforeach
                <textarea name="cta_text" rows="2" class="rounded-xl border border-slate-300 px-3 py-2 sm:col-span-2" placeholder="CTA description">{{ $homepageSettings['cta_text'] }}</textarea>
            </div>
        </div>
        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save homepage settings</button>
    </form>
    <script>
        (() => {
            const list = document.querySelector('[data-faq-list]');
            const addButton = document.querySelector('[data-add-faq]');
            if (!list || !addButton) return;

            const renumber = () => {
                list.querySelectorAll('[data-faq-item]').forEach((item, index) => {
                    item.querySelector('input').name = `faq_items[${index}][title]`;
                    item.querySelector('textarea').name = `faq_items[${index}][text]`;
                });
            };

            list.addEventListener('click', (event) => {
                if (event.target.closest('[data-remove-faq]')) {
                    event.target.closest('[data-faq-item]').remove();
                    renumber();
                }
            });

            addButton.addEventListener('click', () => {
                const item = document.createElement('div');
                item.className = 'rounded-xl border border-slate-200 p-3';
                item.setAttribute('data-faq-item', '');
                item.innerHTML = '<input class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Title"><textarea rows="2" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Description"></textarea><button type="button" data-remove-faq class="mt-2 rounded-lg px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50">Delete FAQ</button>';
                list.appendChild(item);
                renumber();
                item.querySelector('input').focus();
            });
        })();
    </script>
    @endif
    @if(!($isHomePageSection ?? false))
    <div class="mt-8 grid gap-6 {{ $isHeroSection ? 'grid-cols-1' : 'xl:grid-cols-[1.3fr_0.7fr]' }}">
        <div class="space-y-6 {{ $isHeroSection ? 'hidden' : '' }}">
            @forelse($settings as $group => $items)
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <h2 class="border-b border-slate-200 px-6 py-4 font-bold">{{ Str::headline($group ?: 'General') }}</h2>
                    <div class="divide-y divide-slate-100">
                        @foreach($items as $setting)
                            <div class="flex items-center justify-between gap-4 px-6 py-4">
                                <div>
                                    <p class="font-semibold">{{ Str::headline(str_replace('_', ' ', $setting->key)) }}</p>
                                    <p class="text-sm text-slate-500">{{ $setting->key }}</p>
                                </div>
                                <span class="max-w-xs truncate text-sm text-slate-600">{{ $setting->value ?: 'Not configured' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white p-10 text-center text-slate-500 ring-1 ring-slate-200">No settings configured yet.</div>
            @endforelse
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 {{ $isHeroSection ? 'hidden' : '' }}">
            <h2 class="text-xl font-black">System settings</h2>
            <p class="mt-2 text-sm text-slate-500">These values are used globally across the storefront.</p>

            <form method="POST" action="{{ route('admin.settings.system') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Global brand color</label>
                    <div class="flex gap-2">
                        <input type="color" name="brand_color" value="{{ old('brand_color', $systemSettings['brand_color']) }}" class="h-10 w-14 rounded border border-slate-300 p-1">
                        <input type="text" value="{{ old('brand_color', $systemSettings['brand_color']) }}" readonly class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Website name</label>
                    <input type="text" name="site_name" required value="{{ old('site_name', $systemSettings['site_name']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Website slogan</label>
                    <input type="text" name="slogan" maxlength="160" value="{{ old('slogan', $systemSettings['slogan'] ?? '') }}" placeholder="আপনার উদ্যোগ, আমাদের প্ল্যাটফর্ম" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <p class="mt-1 text-xs text-slate-500">Shown beside the website name in the browser tab.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Logo</label>
                    <input type="file" name="logo" accept="image/*" data-branding-input="logo" data-preview-target="logo-preview" class="w-full rounded-xl border border-slate-300 p-2 text-sm">
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $systemSettings['logo_url'] ?? '' }}" alt="Logo preview" data-branding-preview="logo-preview" class="{{ !empty($systemSettings['logo_url']) ? '' : 'hidden' }} h-10 max-w-[170px] rounded-lg object-contain ring-1 ring-slate-200">
                        @if (!empty($systemSettings['logo']))
                            <label class="text-xs text-red-600"><input type="checkbox" name="delete_logo" value="1" class="mr-1"> Delete current logo</label>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Website icon / favicon</label>
                    <input type="file" name="website_icon" accept="image/*,.ico" data-branding-input="website_icon" data-preview-target="website-icon-preview" class="w-full rounded-xl border border-slate-300 p-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Used in the browser tab. Recommended: square PNG or favicon.</p>
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $systemSettings['website_icon_url'] ?? '' }}" alt="Website icon preview" data-branding-preview="website-icon-preview" class="h-10 w-10 rounded-lg object-contain ring-1 ring-slate-200">
                        @if (!empty($systemSettings['website_icon']))
                            <label class="text-xs text-red-600"><input type="checkbox" name="delete_website_icon" value="1" class="mr-1"> Delete current icon</label>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Website address</label>
                    <input type="url" name="site_url" value="{{ old('site_url', $systemSettings['site_url']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Phone</label><input type="text" name="phone" value="{{ old('phone', $systemSettings['phone']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" value="{{ old('email', $systemSettings['email']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">WhatsApp number</label><input type="text" name="whatsapp" value="{{ old('whatsapp', $systemSettings['whatsapp']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Business address</label><input type="text" name="address" value="{{ old('address', $systemSettings['address']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                </div>
                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save system settings</button>
            </form>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 {{ $isHeroSection ? 'hidden' : '' }}">
            <h2 class="text-xl font-black">Entrepreneur profit</h2>
            <p class="mt-2 text-sm text-slate-500">Set the entrepreneur's profit as a percentage of the product purchase cost.</p>
            <form method="POST" action="{{ route('admin.settings.entrepreneur-profit') }}" class="mt-6 space-y-4">
                @csrf
                <label class="block text-sm font-medium text-slate-700">Profit percentage
                    <div class="mt-1 flex items-center gap-2">
                        <input type="number" name="profit_percent" min="0" max="100" step="0.01" required value="{{ old('profit_percent', $entrepreneurProfitPercent) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                        <span class="font-bold text-slate-500">%</span>
                    </div>
                </label>
                <label class="block text-sm font-medium text-slate-700">Return cost per item
                    <div class="mt-1 flex items-center gap-2">
                        <input type="number" name="return_cost" min="0" step="0.01" required value="{{ old('return_cost', $entrepreneurReturnCost) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                        <span class="font-bold text-slate-500">₹</span>
                    </div>
                </label>
                <p class="text-xs text-slate-500">Example: purchase cost ₹100 and 20% means ₹20 profit. Profit cannot exceed the available selling margin.</p>
                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-500">Save profit rate</button>
            </form>
        </div>
        <div id="hero-customization" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 {{ $isHeroSection ? 'mx-auto w-full max-w-4xl' : '' }}">
            <div class="border-b border-slate-100 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Homepage appearance</p>
                <h2 class="mt-1 text-xl font-black">Hero section</h2>
            </div>
            <p class="mt-2 text-sm text-slate-500">Customize the homepage hero banner, call-to-action buttons, colors and images.</p>

            <form method="POST" action="{{ route('admin.settings.homepage') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="settings_scope" value="hero">
                <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                    <span class="font-medium text-slate-700">Hero banner enabled</span>
                    <input type="checkbox" name="hero_enabled" value="1" @checked($homepageSettings['hero_enabled']) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                </label>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hero title</label>
                    <input type="text" name="hero_title" value="{{ old('hero_title', $homepageSettings['hero_title']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hero subtitle</label>
                    <textarea name="hero_subtitle" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">{{ old('hero_subtitle', $homepageSettings['hero_subtitle']) }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hero button label</label>
                    <input type="text" name="hero_button_label" value="{{ old('hero_button_label', $homepageSettings['hero_button_label']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Badge text</label><input name="hero_badge" value="{{ old('hero_badge', $homepageSettings['hero_badge']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Background color</label><input type="color" name="hero_background_color" value="{{ old('hero_background_color', $homepageSettings['hero_background_color']) }}" class="h-10 w-full rounded-xl border border-slate-300 p-1"></div>
                </div>
                <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3"><span class="font-medium text-slate-700">Dark overlay on uploaded banner</span><input type="checkbox" name="hero_overlay_enabled" value="1" @checked($homepageSettings['hero_overlay_enabled']) class="h-4 w-4 rounded"></label>
                <div class="grid gap-4 rounded-xl border border-slate-200 p-3 sm:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm font-semibold sm:col-span-2"><input type="checkbox" name="hero_primary_button_enabled" value="1" @checked($homepageSettings['hero_primary_button_enabled'])> Show primary button</label>
                    <input name="hero_primary_button_label" placeholder="Primary button label" value="{{ old('hero_primary_button_label', $homepageSettings['hero_primary_button_label']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                    <input name="hero_primary_button_url" placeholder="/shop or https://..." value="{{ old('hero_primary_button_url', $homepageSettings['hero_primary_button_url']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                    <label class="flex items-center gap-2 text-sm font-semibold sm:col-span-2"><input type="checkbox" name="hero_secondary_button_enabled" value="1" @checked($homepageSettings['hero_secondary_button_enabled'])> Show secondary button</label>
                    <input name="hero_secondary_button_label" placeholder="Secondary button label" value="{{ old('hero_secondary_button_label', $homepageSettings['hero_secondary_button_label']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                    <input name="hero_secondary_button_url" placeholder="/entrepreneur/register or https://..." value="{{ old('hero_secondary_button_url', $homepageSettings['hero_secondary_button_url']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                </div>

                <div class="space-y-3 rounded-xl border border-slate-200 p-3">
                    <p class="font-medium text-slate-700">Hero slider images</p>
                    @foreach(['slider_image_1' => 'Slider image 1', 'slider_image_2' => 'Slider image 2', 'slider_image_3' => 'Slider image 3'] as $field => $label)
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</label>
                            <input type="file" name="{{ $field }}" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-orange-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-orange-700 hover:file:bg-orange-200">
                            @php $imagePath = $homepageSettings[$field] ?? null; @endphp
                            @if($imagePath)
                                <img src="{{ asset('storage/media/'.$imagePath) }}" alt="{{ $label }}" class="mt-2 h-20 w-full rounded-xl object-cover ring-1 ring-slate-200">
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save hero settings</button>
            </form>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 {{ $isHeroSection ? 'hidden' : '' }}">
        <h2 class="text-xl font-black">Database backup</h2>
        <p class="mt-2 text-sm text-slate-500">Export or restore the complete MySQL database, including products, categories, orders and settings. Import automatically creates a private pre-import backup before changing any data.</p>
        <div class="mt-5 flex flex-wrap items-end gap-5">
            <a href="{{ route('admin.settings.database.export') }}" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700">Export MySQL backup</a>
            <form method="POST" action="{{ route('admin.settings.database.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3" onsubmit="return confirm('Importing will replace the current database. A private pre-import backup will be created first. Continue?')">
                @csrf
                <label class="text-sm font-semibold">MySQL .sql backup file<input type="file" name="database" accept=".sql,.txt" required class="mt-2 block rounded-xl border border-slate-300 p-2 text-sm"></label>
                <button class="rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-500">Import MySQL backup</button>
            </form>
        </div>
    </div>
    @endif
</section>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-branding-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const preview = document.querySelector(`[data-branding-preview="${input.dataset.previewTarget}"]`);
        const file = input.files?.[0];
        if (!preview || !file) return;
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    });
});
</script>
@endpush
