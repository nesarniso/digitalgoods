@extends('layouts.storefront')
@section('title', 'Notifications | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
    <div class="flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Account</p>
            <h1 class="mt-2 text-4xl font-black">Notifications</h1>
        </div>
        <a href="{{ route('account.index') }}" class="text-sm font-semibold text-indigo-700">Back to account</a>
    </div>

    <div class="mt-8 divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @forelse($notifications as $notification)
            @php($data = $notification->data)
            <div class="flex items-start justify-between gap-4 px-6 py-5 {{ $notification->read_at ? '' : 'bg-indigo-50/40' }}">
                <div>
                    <p class="font-bold">{{ $data['title'] ?? 'Notification' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $data['message'] ?? '' }}</p>
                    <p class="mt-2 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @unless($notification->read_at)
                    <form method="POST" action="{{ route('account.notifications.read', $notification->id) }}">
                        @csrf
                        <input type="hidden" name="url" value="{{ $data['url'] ?? '' }}">
                        <button class="text-xs font-bold text-indigo-700">Mark read</button>
                    </form>
                @endunless
            </div>
        @empty
            <p class="px-6 py-12 text-center text-slate-500">You have no notifications.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
</section>
@endsection
