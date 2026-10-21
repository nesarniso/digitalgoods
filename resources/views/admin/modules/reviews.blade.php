@extends('layouts.admin')
@section('title', 'Product reviews')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">Product reviews</h1>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><div class="divide-y divide-slate-100">
        @forelse($reviews as $review)
            <form method="POST" action="{{ route('admin.reviews.status', $review) }}" class="flex flex-wrap items-center justify-between gap-4 p-5">@csrf @method('PUT')
                <div><p class="font-bold">{{ $review->product->name }} · {{ $review->rating }}/5</p><p class="text-sm text-slate-500">{{ $review->user->name }} · Order {{ $review->order->order_number }} · {{ $review->title ?: 'No title' }}</p><p class="mt-1 text-sm">{{ $review->body ?: 'No review text.' }}</p></div>
                <div class="flex items-center gap-2"><select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected($review->status === $status)>{{ Str::headline($status) }}</option>@endforeach</select><button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save</button></div>
            </form>
        @empty <p class="p-10 text-center text-slate-500">No reviews yet.</p> @endforelse
    </div></div>
    <div class="mt-6">{{ $reviews->links() }}</div>
</section>
@endsection
