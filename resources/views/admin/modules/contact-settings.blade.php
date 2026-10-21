@extends('layouts.admin')
@section('title', 'Contact page')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div>
        <h1 class="text-3xl font-black">Contact page</h1>
        <p class="mt-2 text-slate-500">Edit the contact page content and manage its FAQ items.</p>
    </div>
    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.settings.contact.save') }}" class="mt-8 space-y-6">
        @csrf
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Hero and form text</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <input name="badge" value="{{ old('badge', $contactSettings['badge']) }}" placeholder="Hero badge" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="form_eyebrow" value="{{ old('form_eyebrow', $contactSettings['form_eyebrow']) }}" placeholder="Form eyebrow" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="hero_title" value="{{ old('hero_title', $contactSettings['hero_title']) }}" placeholder="Hero title" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="form_title" value="{{ old('form_title', $contactSettings['form_title']) }}" placeholder="Form title" class="rounded-xl border border-slate-300 px-3 py-2">
                <textarea name="hero_text" rows="3" placeholder="Hero description" class="rounded-xl border border-slate-300 px-3 py-2">{{ old('hero_text', $contactSettings['hero_text']) }}</textarea>
                <textarea name="form_text" rows="3" placeholder="Form description" class="rounded-xl border border-slate-300 px-3 py-2">{{ old('form_text', $contactSettings['form_text']) }}</textarea>
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Support details</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <input name="support_hours" value="{{ old('support_hours', $contactSettings['support_hours']) }}" placeholder="Support hours" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="response_time" value="{{ old('response_time', $contactSettings['response_time']) }}" placeholder="Response time" class="rounded-xl border border-slate-300 px-3 py-2">
            </div>
            <p class="mt-3 text-sm text-slate-500">Phone, email, WhatsApp and address are managed from global System settings.</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div><h2 class="text-xl font-black">Contact page FAQ</h2><p class="mt-1 text-sm text-slate-500">Edit, remove or add questions shown on the contact page.</p></div>
                <button type="button" data-add-faq class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-bold text-indigo-700 hover:bg-indigo-50">Add FAQ</button>
            </div>
            <div data-faq-list class="mt-5 space-y-4">
                @foreach($contactSettings['faq_items'] as $index => $item)
                    <div data-faq-item class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start gap-3">
                            <div class="grid flex-1 gap-3">
                                <input name="faq_items[{{ $index }}][question]" value="{{ $item['question'] }}" placeholder="Question" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                                <textarea name="faq_items[{{ $index }}][answer]" rows="2" placeholder="Answer" class="w-full rounded-xl border border-slate-300 px-3 py-2">{{ $item['answer'] }}</textarea>
                            </div>
                            <button type="button" data-remove-faq class="rounded-lg px-2 py-1 text-sm font-bold text-red-600 hover:bg-red-50">Delete</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <input name="faq_eyebrow" value="{{ old('faq_eyebrow', $contactSettings['faq_eyebrow']) }}" placeholder="FAQ eyebrow" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="faq_title" value="{{ old('faq_title', $contactSettings['faq_title']) }}" placeholder="FAQ title" class="rounded-xl border border-slate-300 px-3 py-2">
            </div>
        </div>
        <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Save contact page</button>
    </form>
</section>
<script>
    (() => {
        const list = document.querySelector('[data-faq-list]');
        const add = document.querySelector('[data-add-faq]');
        if (!list || !add) return;
        let index = list.querySelectorAll('[data-faq-item]').length;
        const remove = (button) => button.closest('[data-faq-item]')?.remove();
        list.addEventListener('click', (event) => {
            if (event.target.closest('[data-remove-faq]')) remove(event.target.closest('[data-remove-faq]'));
        });
        add.addEventListener('click', () => {
            const item = document.createElement('div');
            item.dataset.faqItem = '';
            item.className = 'rounded-xl border border-slate-200 p-4';
            item.innerHTML = `<div class="flex items-start gap-3"><div class="grid flex-1 gap-3"><input name="faq_items[${index}][question]" placeholder="Question" class="w-full rounded-xl border border-slate-300 px-3 py-2"><textarea name="faq_items[${index}][answer]" rows="2" placeholder="Answer" class="w-full rounded-xl border border-slate-300 px-3 py-2"></textarea></div><button type="button" data-remove-faq class="rounded-lg px-2 py-1 text-sm font-bold text-red-600 hover:bg-red-50">Delete</button></div>`;
            list.append(item);
            index += 1;
        });
    })();
</script>
@endsection
