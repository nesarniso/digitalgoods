@extends('layouts.storefront')
@section('title', 'Blog | '.config('app.name'))
@section('meta_description', 'Ideas, practical guides, and inspiration for creators and digital entrepreneurs.')
@section('canonical_url', route('blog.index'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14">
    <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-rose-50 px-6 py-10 shadow-sm sm:px-10 sm:py-12">
        <nav class="text-sm font-semibold text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600">Home</a><span class="mx-2">/</span><span class="text-slate-800">Blog</span>
        </nav>
        <div class="mt-6 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">Ideas for creators</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">Blog</h1>
                <p class="mt-4 text-base leading-7 text-slate-600">Practical guides and fresh ideas to help you create, edit, and share better content.</p>
            </div>
            <form method="GET" action="{{ route('blog.index') }}" class="flex w-full max-w-md gap-2">
                <label for="blog-search" class="sr-only">Search blog posts</label>
                <input id="blog-search" type="search" name="q" value="{{ $search }}" placeholder="Search articles" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                <button class="rounded-xl bg-[#f58220] px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Search</button>
            </form>
        </div>
    </div>

    <div class="mt-9 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-orange-600">From the {{ config('app.name') }} team</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Latest articles</h2>
        </div>
        <p class="text-sm text-slate-500">{{ number_format($posts->total()) }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}</p>
    </div>

    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg">
                <a href="{{ route('blog.show', $post) }}" class="relative block aspect-[16/10] overflow-hidden bg-orange-50" aria-label="Read {{ app(\App\Support\SiteBrand::class)->text($post->title) }}">
                    @if($post->featured_image_url)
                        <img src="{{ $post->featured_image_url }}" alt="{{ app(\App\Support\SiteBrand::class)->text($post->featured_image_alt ?: $post->title) }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                    @else
                        <div class="flex h-full items-center justify-center bg-gradient-to-br from-orange-100 to-rose-100 text-5xl font-black text-orange-400">{{ Str::upper(Str::substr(config('app.name'), 0, 1)) }}</div>
                    @endif
                    <time datetime="{{ $post->published_at->toDateString() }}" class="absolute left-3 top-3 rounded-xl bg-white px-3 py-2 text-center shadow-sm">
                        <span class="block text-lg font-black leading-none text-slate-900">{{ $post->published_at->format('d') }}</span>
                        <span class="mt-1 block text-[10px] font-bold uppercase tracking-wide text-orange-600">{{ $post->published_at->format('M') }}</span>
                    </time>
                </a>
                <div class="flex flex-1 flex-col p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
                        <span class="rounded-full bg-orange-50 px-3 py-1 font-bold text-orange-700">Blog</span>
                        <span>{{ $post->published_at->format('M j, Y') }}</span>
                    </div>
                    <h2 class="mt-4 line-clamp-2 text-lg font-black leading-6 text-slate-900 transition group-hover:text-orange-700">
                        <a href="{{ route('blog.show', $post) }}">{{ app(\App\Support\SiteBrand::class)->text($post->title) }}</a>
                    </h2>
                    <p class="mt-3 line-clamp-3 flex-1 text-sm leading-6 text-slate-600">{{ app(\App\Support\SiteBrand::class)->text($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags(preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $post->content)), 180)) }}</p>
                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <span class="truncate text-xs font-medium text-slate-500">By {{ app(\App\Support\SiteBrand::class)->text($post->author_name ?: config('app.name')) }}</span>
                        <a href="{{ route('blog.show', $post) }}" class="shrink-0 text-sm font-bold text-orange-600 hover:text-orange-800">Continue reading <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center sm:col-span-2 lg:col-span-3">
                <p class="text-lg font-bold text-slate-800">{{ $search ? 'No matching articles found.' : 'No articles published yet.' }}</p>
                @if($search)<a href="{{ route('blog.index') }}" class="mt-3 inline-block text-sm font-bold text-orange-600 hover:underline">Clear search</a>@endif
            </div>
        @endforelse
    </div>
    @if($posts->hasPages())
        <div class="mt-9">{{ $posts->links() }}</div>
    @endif
</section>
@endsection
