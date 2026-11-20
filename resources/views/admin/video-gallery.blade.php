@extends('layouts.admin')
@section('title', 'Video gallery | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">Store media</p>
    <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">Video gallery</h1>
            <p class="mt-2 text-slate-500">Manage, replace, and download product preview videos.</p>
        </div>
        <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-sm font-bold text-indigo-700">{{ number_format($videos->total()) }} videos</span>
    </div>

    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif

    <form method="GET" action="{{ route('admin.video-gallery') }}" class="mt-7 flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search title or product" class="min-w-[220px] flex-1 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm">
        <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white">Search</button>
        @if($search)<a href="{{ route('admin.video-gallery') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Clear</a>@endif
    </form>

    <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($videos as $video)
            <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <video controls playsinline preload="metadata" class="aspect-[9/16] max-h-[420px] w-full bg-slate-950 object-contain">
                    <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                    Your browser does not support video playback.
                </video>
                <div class="p-4">
                    <p class="truncate text-sm font-bold text-slate-900" title="{{ $video->title }}">{{ $video->title ?: 'Untitled video' }}</p>
                    <p class="mt-1 truncate text-xs text-slate-500">
                        @if($video->product)
                            {{ $video->product->name }}{{ $video->product->trashed() ? ' (archived)' : '' }}
                        @else
                            Product unavailable
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-slate-400">{{ strtoupper(str_replace('video/', '', $video->mime_type)) }} · Added {{ $video->created_at?->format('M j, Y') }}</p>

                    @if($video->product)
                        <form method="POST" action="{{ route('admin.video-gallery.update', $video) }}" enctype="multipart/form-data" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                            @csrf @method('PUT')
                            <label class="block text-xs font-semibold text-slate-600">Title
                                <input name="title" maxlength="255" value="{{ $video->title }}" class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                            </label>
                            <label class="block text-xs font-semibold text-slate-600">Replace video <span class="font-normal text-slate-400">(optional)</span>
                                <input type="file" name="video" accept="video/mp4,video/webm,.mp4,.webm" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white p-2 text-xs font-normal">
                            </label>
                            <button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700">Save changes</button>
                        </form>
                    @endif

                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.video-gallery.download', $video) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-center text-xs font-bold text-slate-700 hover:bg-slate-50">Download</a>
                        @if($video->product)
                            <form method="POST" action="{{ route('admin.video-gallery.destroy', $video) }}" onsubmit="return confirm('Delete this product video?')">
                                @csrf @method('DELETE')
                                <button class="w-full rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                            </form>
                        @endif
                    </div>
                    @if($video->product && ! $video->product->trashed())
                        <a href="{{ route('admin.products.edit', $video->product) }}" class="mt-3 block text-center text-xs font-semibold text-indigo-700 hover:underline">Open product</a>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-xl bg-white p-8 text-sm text-slate-500 sm:col-span-2 lg:col-span-4">No product videos found.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $videos->links() }}</div>
</section>
@endsection
