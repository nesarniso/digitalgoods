@extends('layouts.entrepreneur')

@section('title', 'Notifications')
@section('header_title', 'Notifications')
@section('content')
<main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-10">
    <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Updates</p><h1 class="mt-2 text-3xl font-black">Notifications</h1><p class="mt-2 text-slate-600">Stay informed about orders, profit releases, refunds and withdrawals.</p></div>
    <div class="mt-8 divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @forelse($notifications as $notification)
            @php($data = $notification->data)
            <div class="flex items-start justify-between gap-4 px-6 py-5 {{ $notification->read_at ? '' : 'bg-indigo-50/40' }}"><div><p class="font-bold">{{ $data['title'] ?? 'Notification' }}</p><p class="mt-1 text-sm text-slate-600">{{ $data['message'] ?? '' }}</p><p class="mt-2 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p></div>@unless($notification->read_at)<form method="POST" action="{{ route('entrepreneur.notifications.read', $notification->id) }}">@csrf<input type="hidden" name="url" value="{{ $data['url'] ?? '' }}"><button class="text-xs font-bold text-[var(--brand-primary)]">Mark read</button></form>@endunless</div>
        @empty
            <p class="px-6 py-12 text-center text-slate-500">You have no notifications.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $notifications->links() }}</div>
</main>
@endsection
