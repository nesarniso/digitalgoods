@extends('layouts.admin')
@section('title', 'Push notifications')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">Push notification composer</h1>
    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_1.4fr]">
        <form method="POST" action="{{ route('admin.notifications.store') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf
            <label class="text-sm font-semibold">Title</label><input name="title" required maxlength="150" class="mt-1 w-full rounded-xl border p-3">
            <label class="mt-4 block text-sm font-semibold">Message</label><textarea name="body" required rows="5" class="mt-1 w-full rounded-xl border p-3"></textarea>
            <label class="mt-4 block text-sm font-semibold">Click URL (optional)</label><input type="url" name="url" class="mt-1 w-full rounded-xl border p-3">
            <label class="mt-4 block text-sm font-semibold">Audience</label><select name="audience" class="mt-1 w-full rounded-xl border p-3"><option value="all">All subscribed devices</option><option value="customers">Signed-in customers</option></select>
            <label class="mt-4 block text-sm font-semibold">Schedule (optional)</label><input type="datetime-local" name="scheduled_at" min="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-xl border p-3">
            <div class="mt-5 flex gap-2"><button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Queue / schedule</button></div>
        </form>
        <form method="POST" action="{{ route('admin.notifications.test') }}" class="rounded-2xl bg-indigo-50 p-6 ring-1 ring-indigo-100">@csrf
            <h2 class="font-black">Test send</h2><p class="mt-1 text-sm text-slate-600">Send to your active browser subscriptions.</p>
            <input name="title" required placeholder="Test title" class="mt-3 w-full rounded-xl border p-3"><textarea name="body" required placeholder="Test message" class="mt-2 w-full rounded-xl border p-3"></textarea><button class="mt-3 rounded-xl bg-indigo-600 px-4 py-2 font-bold text-white">Send test</button>
        </form>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="grid grid-cols-3 gap-3">@foreach([['Sent', $deliveryStats['sent']], ['Failed', $deliveryStats['failed']], ['Clicked', $deliveryStats['clicked']]] as [$label, $value])<div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">{{ $label }}</p><p class="mt-1 text-2xl font-black">{{ number_format($value) }}</p></div>@endforeach</div>
            <h2 class="mt-8 font-bold">Campaign history</h2>
            <div class="mt-3 divide-y">@forelse($campaigns as $campaign)<div class="flex items-center justify-between py-3"><div><p class="font-semibold">{{ $campaign->title }}</p><p class="text-xs text-slate-500">{{ $campaign->status }} · {{ $campaign->recipient_count }} recipients</p></div><span class="text-sm text-slate-500">{{ $campaign->clicks }} clicks</span></div>@empty<p class="py-8 text-slate-500">No notifications yet.</p>@endforelse</div>
            <a href="{{ route('admin.notifications.export', request()->query()) }}" class="mt-4 inline-block text-sm font-bold text-indigo-600">Export delivery CSV</a>{{ $campaigns->links() }}
        </div>
    </div>
</section>
@endsection
