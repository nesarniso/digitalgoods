@extends('layouts.storefront')

@section('title', 'Become an Entrepreneur | '.config('app.name'))
@section('meta_description', 'Join the eflix platform and grow your business by promoting quality products without managing inventory or delivery.')

@section('content')
<main>
    <section class="relative overflow-hidden bg-slate-950 text-white">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[var(--brand-primary)]/20 blur-3xl"></div>
        <div class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16 lg:py-20">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[var(--brand-primary)]">Become an Entrepreneur</p>
                <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-6xl">আপনার উদ্যোগ,<br><span class="text-[var(--brand-primary)]">eflix platform-এর</span> সাথে</h1>
                <p class="mt-6 max-w-xl text-base leading-8 text-slate-300">নিজের network ব্যবহার করে quality products promote করুন, customer order নিন এবং inventory, delivery ও operations eflix platform-এর উপর ছেড়ে দিন।</p>
                <div class="mt-8 flex flex-wrap gap-3 text-sm font-bold">
                    <a href="#apply" class="rounded-xl bg-[var(--brand-primary)] px-5 py-3.5 text-white shadow-lg transition hover:opacity-90">Apply now <span class="ml-1">→</span></a>
                    <a href="#how-it-works" class="rounded-xl border border-white/20 px-5 py-3.5 text-white transition hover:bg-white/10">How it works</a>
                </div>
                <div class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-white/10 pt-6">
                    <div><p class="text-2xl font-black text-[var(--brand-primary)]">01</p><p class="mt-1 text-xs text-slate-400">Apply online</p></div>
                    <div><p class="text-2xl font-black text-[var(--brand-primary)]">02</p><p class="mt-1 text-xs text-slate-400">Share products</p></div>
                    <div><p class="text-2xl font-black text-[var(--brand-primary)]">03</p><p class="mt-1 text-xs text-slate-400">Earn from sales</p></div>
                </div>
            </div>
        </div>
    </section>
    <section id="apply" class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-14">
        <div class="mx-auto max-w-3xl rounded-3xl bg-white p-5 shadow-xl shadow-slate-200/70 ring-1 ring-slate-200 sm:p-8">
            <p class="text-xs font-black uppercase tracking-widest text-[var(--brand-primary)]">Start your application</p>
            <h2 class="mt-2 text-2xl font-black">Join eflix platform</h2>
            <p class="mt-2 text-sm text-slate-500">আপনার তথ্য দিয়ে আবেদন করুন। Review-এর পর account active করা হবে।</p>
            <form method="POST" action="{{ route('entrepreneur.register.store') }}" class="mt-6 space-y-4">
                @csrf
                @if ($errors->any())
                    <div class="rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold">Name<input name="name" value="{{ old('name') }}" required placeholder="Your full name" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"></label>
                    <label class="text-sm font-semibold">Mobile<input name="phone" value="{{ old('phone') }}" required placeholder="01XXXXXXXXX" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"></label>
                </div>
                <label class="block text-sm font-semibold">Email <span class="font-normal text-red-500">(required for login)</span><input name="email" value="{{ old('email') }}" required type="email" placeholder="you@example.com" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"></label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold">Division<select name="division" data-entrepreneur-division required class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"><option value="">Select your division</option>@foreach($divisions as $division => $divisionDistricts)<option value="{{ $division }}" @selected(old('division') === $division)>{{ $division }}</option>@endforeach</select></label>
                    <label class="text-sm font-semibold">District<select name="district" data-entrepreneur-district required class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"><option value="">Select division first</option>@foreach($districts as $district)<option value="{{ $district }}" data-division="{{ collect($divisions)->search(fn ($items) => in_array($district, $items, true)) }}" hidden disabled @selected(old('district') === $district)>{{ $district }}</option>@endforeach</select></label>
                    <label class="text-sm font-semibold">Upazila / Thana<select name="upazila" data-entrepreneur-upazila required class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"><option value="">Select district first</option></select></label>
                    <label class="text-sm font-semibold sm:col-span-2">Address / Village / Road<textarea name="address" required placeholder="Village, road name, house details" class="mt-1.5 min-h-24 w-full rounded-xl border border-slate-300 bg-white p-3">{{ old('address') }}</textarea></label>
                </div>
                <label class="block text-sm font-semibold">Referral code <span class="font-normal text-slate-400">(optional)</span><input name="referral_code" value="{{ old('referral_code', $referralCode ?? '') }}" placeholder="ENT12345" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"></label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold">Password<input name="password" required type="password" placeholder="Minimum 8 characters" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"></label>
                    <label class="text-sm font-semibold">Confirm password<input name="password_confirmation" required type="password" placeholder="Repeat password" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white p-3"></label>
                </div>
                <button type="submit" class="w-full rounded-xl px-6 py-3.5 text-sm font-black text-white shadow-lg transition hover:opacity-90 focus:outline-none focus:ring-4 sm:w-auto" style="background-color: var(--brand-primary);">Submit application →</button>
                <p class="text-center text-xs text-slate-500">Already an entrepreneur? <a href="{{ route('entrepreneur.login') }}" class="font-bold text-[var(--brand-primary)] hover:underline">Sign in</a></p>
            </form>
        </div>
    </section>
    <script>
        (() => {
            const division = document.querySelector('[data-entrepreneur-division]');
            const district = document.querySelector('[data-entrepreneur-district]');
            const upazila = document.querySelector('[data-entrepreneur-upazila]');
            if (!division || !district || !upazila) return;
            const divisions = @json($divisions);
            const upazilas = @json($upazilas);
            const selectedUpazila = @json(old('upazila'));
            const renderLocation = () => {
                const allowed = divisions[division.value] || [];
                district.querySelector('option[value=""]').textContent = division.value ? 'Select your district' : 'Select division first';
                Array.from(district.options).forEach((option) => {
                    if (!option.value) return;
                    const visible = allowed.includes(option.value);
                    option.hidden = !visible;
                    option.disabled = !visible;
                    if (!visible && option.selected) option.selected = false;
                });
                if (!allowed.includes(district.value)) district.value = '';
                upazila.innerHTML = '<option value="">Select district first</option>';
                (upazilas[district.value] || []).forEach((item) => {
                    const option = new Option(item, item);
                    option.selected = item === selectedUpazila;
                    upazila.add(option);
                });
            };
            division.addEventListener('change', () => {
                district.value = '';
                renderLocation();
            });
            district.addEventListener('change', renderLocation);
            renderLocation();
        })();
    </script>

    <section id="how-it-works" class="mx-auto max-w-5xl px-5 py-14 sm:px-8 sm:py-16">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--brand-primary)]">Why join eflix?</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">একটি business শুরু করার জন্য যা যা দরকার</h2>
            <p class="mt-4 leading-7 text-slate-500">আপনি marketing ও customer-এর দিকে focus করবেন; platform-এর core operations eflix manage করবে।</p>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['icon' => '▣', 'title' => 'Ready products', 'text' => 'Curated products এবং clear pricing থেকে সহজে product বেছে নিন।'],
                ['icon' => '↗', 'title' => 'Sell anywhere', 'text' => 'Facebook, WhatsApp, local community বা personal network-এ share করুন।'],
                ['icon' => '✓', 'title' => 'No inventory stress', 'text' => 'আগে থেকে stock বা warehouse রাখার প্রয়োজন নেই।'],
                ['icon' => '৳', 'title' => 'Track your profit', 'text' => 'Dashboard-এ order, sale এবং eligible profit track করুন।'],
            ] as $benefit)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 text-xl font-black text-[var(--brand-primary)]">{{ $benefit['icon'] }}</span>
                    <h3 class="mt-5 text-lg font-black text-slate-900">{{ $benefit['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $benefit['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-orange-50 px-6 py-16">
        <div class="mx-auto grid max-w-5xl gap-10 px-5 sm:px-8 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--brand-primary)]">How it works</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">আপনার journey, চারটি সহজ ধাপে</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach([
                    ['n' => '01', 'title' => 'Register', 'text' => 'Application form পূরণ করে account তৈরি করুন।'],
                    ['n' => '02', 'title' => 'Get approved', 'text' => 'আমাদের team তথ্য review করে approval দেবে।'],
                    ['n' => '03', 'title' => 'Promote', 'text' => 'Product link ও details customer-এর সাথে share করুন।'],
                    ['n' => '04', 'title' => 'Grow', 'text' => 'Successful sales-এর সাথে নিজের business grow করুন।'],
                ] as $step)
                    <div class="rounded-2xl bg-white p-5 shadow-sm"><span class="text-sm font-black text-[var(--brand-primary)]">{{ $step['n'] }}</span><h3 class="mt-2 font-black text-slate-900">{{ $step['title'] }}</h3><p class="mt-1 text-sm leading-6 text-slate-500">{{ $step['text'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-5 py-14 sm:px-8 sm:py-16">
        <div class="rounded-3xl bg-slate-900 p-8 text-center text-white sm:p-12">
            <h2 class="text-3xl font-black">আজ থেকেই শুরু করুন</h2>
            <p class="mx-auto mt-3 max-w-xl leading-7 text-slate-300">আপনার network, আমাদের platform—একসাথে একটি sustainable online business তৈরি করুন।</p>
            <a href="#apply" class="mt-7 inline-flex rounded-xl px-6 py-3.5 font-black text-white transition hover:opacity-90" style="background-color: var(--brand-primary);">Apply to eflix platform →</a>
        </div>
    </section>
</main>
@endsection
