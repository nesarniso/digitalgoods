@extends('layouts.admin')
@section('title', $page->exists ? 'Edit page' : 'Add page')
@section('content')
<section class="mx-auto min-w-0 max-w-5xl px-4 py-8 sm:px-6 sm:py-10 lg:px-10"><a href="{{ route('admin.settings.pages') }}" class="text-sm font-semibold text-indigo-700">← Back to pages</a><h1 class="mt-5 break-words text-3xl font-black">{{ $page->exists ? 'Edit page' : 'Add page' }}</h1><form method="POST" action="{{ $page->exists ? route('admin.settings.pages.update', $page) : route('admin.settings.pages.store') }}" class="mt-8 min-w-0 space-y-5 overflow-hidden rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">@csrf @if($page->exists) @method('PUT') @endif
<div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-semibold">Title</label><input name="title" required value="{{ old('title', $page->title) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3"></div><div><label class="mb-1 block text-sm font-semibold">Slug</label><input name="slug" required value="{{ old('slug', $page->slug) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3"></div></div>
@if(in_array($page->slug, ['faq', 'frequently-asked-questions'], true))
<div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5"><div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"><div class="min-w-0"><h2 class="text-xl font-black">Frequently asked questions</h2><p class="mt-1 text-sm text-slate-500">Add, edit or delete FAQ items. Empty items will not be published.</p></div><button type="button" data-add-faq class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Add question</button></div><div data-faq-list class="mt-5 space-y-4">@foreach($faqItems as $index => $item)<div data-faq-item class="rounded-xl bg-white p-4 ring-1 ring-slate-200"><div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-start"><div class="grid min-w-0 flex-1 gap-3"><input name="faq_items[{{ $index }}][question]" value="{{ $item['question'] ?? '' }}" placeholder="Question" class="w-full min-w-0 rounded-xl border border-slate-300 px-3 py-2"><textarea name="faq_items[{{ $index }}][answer]" rows="3" placeholder="Answer" class="w-full min-w-0 rounded-xl border border-slate-300 px-3 py-2">{{ $item['answer'] ?? '' }}</textarea></div><button type="button" data-remove-faq class="self-end rounded-lg px-2 py-1 text-sm font-bold text-red-600 sm:self-start">Delete</button></div></div>@endforeach</div></div>
@else
<div><label class="mb-1 block text-sm font-semibold">Page content (HTML)</label><textarea name="content" required rows="18" class="w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm">{{ old('content', $page->content) }}</textarea></div>
@endif
<div class="grid gap-4 sm:grid-cols-2"><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" placeholder="SEO meta title" class="rounded-xl border border-slate-300 px-4 py-3"><input name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" placeholder="SEO meta description" class="rounded-xl border border-slate-300 px-4 py-3"></div>
<label class="flex items-center gap-3"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))> Published</label><label class="flex items-center gap-3"><input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', $page->show_in_footer))> Show in footer</label><button class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white">Save page</button></form></section>
@if(in_array($page->slug, ['faq', 'frequently-asked-questions'], true))
<script>
(() => {
    const list = document.querySelector('[data-faq-list]');
    const add = document.querySelector('[data-add-faq]');
    if (!list || !add) return;
    let index = list.querySelectorAll('[data-faq-item]').length;
    list.addEventListener('click', (event) => event.target.closest('[data-remove-faq]')?.closest('[data-faq-item]')?.remove());
    add.addEventListener('click', () => {
        const item = document.createElement('div');
        item.dataset.faqItem = '';
        item.className = 'rounded-xl bg-white p-4 ring-1 ring-slate-200';
        item.innerHTML = `<div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-start"><div class="grid min-w-0 flex-1 gap-3"><input name="faq_items[${index}][question]" placeholder="Question" class="w-full min-w-0 rounded-xl border border-slate-300 px-3 py-2"><textarea name="faq_items[${index}][answer]" rows="3" placeholder="Answer" class="w-full min-w-0 rounded-xl border border-slate-300 px-3 py-2"></textarea></div><button type="button" data-remove-faq class="self-end rounded-lg px-2 py-1 text-sm font-bold text-red-600 sm:self-start">Delete</button></div>`;
        list.append(item);
        index += 1;
    });
})();
</script>
@endif
@endsection
