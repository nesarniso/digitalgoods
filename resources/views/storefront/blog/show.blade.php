@extends('layouts.storefront')
@section('title', app(\App\Support\SiteBrand::class)->text($blogPost->meta_title ?: $blogPost->title).' | '.config('app.name'))
@section('meta_description', app(\App\Support\SiteBrand::class)->text($blogPost->meta_description ?: \Illuminate\Support\Str::limit($blogPost->excerpt ?: strip_tags($blogPost->content), 155)))
@section('canonical_url', route('blog.show', $blogPost))
@section('og_type', 'article')
@section('og_title', app(\App\Support\SiteBrand::class)->text($blogPost->title).' | '.config('app.name'))
@if($blogPost->featured_image_url)
    @section('og_image', $blogPost->featured_image_url)
@endif
@section('content')
<article class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
    <nav class="text-sm font-semibold text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-orange-600">Home</a><span class="mx-2">/</span><a href="{{ route('blog.index') }}" class="hover:text-orange-600">Blog</a><span class="mx-2">/</span>        <span class="text-slate-800">{{ \Illuminate\Support\Str::limit(app(\App\Support\SiteBrand::class)->text($blogPost->title), 55) }}</span>
    </nav>

    <header class="mx-auto mt-7 max-w-4xl text-center">
        <span class="inline-flex rounded-full bg-orange-50 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-orange-700">Creator guide</span>
        <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl">{{ app(\App\Support\SiteBrand::class)->text($blogPost->title) }}</h1>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 text-sm text-slate-500">
            <span>By <strong class="text-slate-700">{{ app(\App\Support\SiteBrand::class)->text($blogPost->author_name ?: config('app.name')) }}</strong></span>
            <span aria-hidden="true">·</span>
            <time datetime="{{ $blogPost->published_at->toIso8601String() }}">{{ $blogPost->published_at->format('F j, Y') }}</time>
        </div>
    </header>

    @if($blogPost->featured_image_url)
        <figure class="mx-auto mt-8 max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-orange-50 shadow-sm">
            <img src="{{ $blogPost->featured_image_url }}" alt="{{ app(\App\Support\SiteBrand::class)->text($blogPost->featured_image_alt ?: $blogPost->title) }}" class="max-h-[560px] w-full object-cover">
        </figure>
    @endif

    <div class="mx-auto mt-8 max-w-4xl rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8 lg:p-10">
        <div class="blog-content break-words text-slate-700">
            {!! app(\App\Support\SiteBrand::class)->html($blogPost->content) !!}
        </div>
        <div class="mt-8 border-t border-slate-200 pt-5">
            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-orange-700 hover:text-orange-900"><span aria-hidden="true">←</span> Back to all articles</a>
        </div>
    </div>

    @if($relatedPosts->isNotEmpty())
        <section class="mt-14" aria-labelledby="related-blog-title">
            <div class="flex items-end justify-between gap-4">
                <div><p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">Keep exploring</p><h2 id="related-blog-title" class="mt-2 text-2xl font-black text-slate-900">More articles</h2></div>
                <a href="{{ route('blog.index') }}" class="text-sm font-bold text-orange-700 hover:underline">View all</a>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($relatedPosts as $relatedPost)
                    <a href="{{ route('blog.show', $relatedPost) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg">
                        @if($relatedPost->featured_image_url)<img src="{{ $relatedPost->featured_image_url }}" alt="{{ app(\App\Support\SiteBrand::class)->text($relatedPost->featured_image_alt ?: $relatedPost->title) }}" loading="lazy" class="aspect-[16/9] w-full object-cover">@endif
                        <div class="p-4"><p class="text-xs font-semibold text-slate-500">{{ $relatedPost->published_at->format('M j, Y') }}</p><h3 class="mt-2 line-clamp-2 font-bold leading-6 text-slate-900 group-hover:text-orange-700">{{ app(\App\Support\SiteBrand::class)->text($relatedPost->title) }}</h3></div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</article>
@endsection
