@extends('layouts.admin')
@section('title', 'Footer settings')
@section('content')
<section class="mx-auto max-w-5xl px-6 py-10 lg:px-10">
    <div>
        <h1 class="text-3xl font-black">Footer settings</h1>
        <p class="mt-2 text-slate-500">Manage the global footer shown consistently across every storefront page.</p>
    </div>
    @if(session('success')) <div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('admin.settings.footer.save') }}" class="mt-8 space-y-6">
        @csrf
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <label class="flex items-center justify-between gap-4"><span class="font-bold">Show global footer</span><input type="checkbox" name="enabled" value="1" @checked($footerSettings['enabled']) class="h-5 w-5 rounded"></label>
            <div class="mt-5"><label class="mb-1 block text-sm font-medium">Footer description</label><textarea name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2">{{ old('description', $footerSettings['description']) }}</textarea></div>
            <div class="mt-4"><label class="mb-1 block text-sm font-medium">Copyright text</label><input name="copyright" value="{{ old('copyright', $footerSettings['copyright']) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2"></div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Footer pages</h2>
            <p class="mt-2 text-sm text-slate-500">Choose which published pages appear in the footer. Edit their content from Website Pages.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @forelse($footerPages as $page)
                    <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                        <span>
                            <span class="block font-semibold">{{ $page->title }}</span>
                            <span class="text-xs text-slate-500">/pages/{{ $page->slug }}</span>
                        </span>
                        <input type="checkbox" name="footer_page_ids[]" value="{{ $page->id }}" @checked($page->show_in_footer) class="h-5 w-5 rounded">
                    </label>
                @empty
                    <p class="text-sm text-slate-500">No website pages have been created yet.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Footer columns</h2>
            @foreach(['one' => 'First column', 'two' => 'Second column'] as $column => $columnLabel)
                <div class="mt-5 rounded-xl border border-slate-200 p-4">
                    <label class="mb-1 block text-sm font-medium">{{ $columnLabel }} title</label>
                    <input name="column_{{ $column }}_title" value="{{ old("column_{$column}_title", $footerSettings["column_{$column}_title"]) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    @foreach($footerSettings["column_{$column}_links"] as $index => $link)
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <input name="column_{{ $column }}_link_{{ $index + 1 }}_label" placeholder="Link label" value="{{ old("column_{$column}_link_".($index + 1)."_label", $link['label']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            <input name="column_{{ $column }}_link_{{ $index + 1 }}_url" placeholder="https://..." value="{{ old("column_{$column}_link_".($index + 1)."_url", $link['url']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Newsletter and social links</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <input name="newsletter_title" placeholder="Newsletter title" value="{{ old('newsletter_title', $footerSettings['newsletter_title']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="facebook_url" placeholder="Facebook URL" value="{{ old('facebook_url', $footerSettings['facebook_url']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="instagram_url" placeholder="Instagram URL" value="{{ old('instagram_url', $footerSettings['instagram_url']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
                <input name="youtube_url" placeholder="YouTube URL" value="{{ old('youtube_url', $footerSettings['youtube_url']) }}" class="rounded-xl border border-slate-300 px-3 py-2">
            </div>
            <textarea name="newsletter_text" rows="2" placeholder="Newsletter description" class="mt-4 w-full rounded-xl border border-slate-300 px-3 py-2">{{ old('newsletter_text', $footerSettings['newsletter_text']) }}</textarea>
        </div>
        <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save footer settings</button>
    </form>
</section>
@endsection
