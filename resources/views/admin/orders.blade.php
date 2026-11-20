@extends('layouts.admin')
@section('title', 'Order management | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Admin</p>
            <h1 class="mt-2 text-3xl font-black sm:text-4xl">Order management</h1>
            <p class="mt-2 text-sm text-slate-500">Select multiple orders and update their fulfilment status together.</p>
        </div>
        <form class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Order number" class="rounded-xl border border-slate-300 px-4 py-2 text-sm">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach (['pending', 'processing', 'delivered'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ Str::headline($status) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
    </div>

    @if (session('success'))
        <div class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.orders.bulk-status') }}" class="mt-7" data-bulk-orders>
        @csrf
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-white">
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" data-select-all class="h-4 w-4 rounded border-white/40">
                Select all on page
            </label>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-300" data-selected-count>0 selected</span>
                <select name="status" required class="rounded-lg border-0 bg-white px-3 py-2 text-sm font-semibold text-slate-900">
                    <option value="">Bulk change status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Paid</option>
                    <option value="processing">Processing</option>
                    <option value="packed">Packed</option>
                    <option value="shipped">Shipped</option>
                    <option value="out_for_delivery">Out for delivery</option>
                    <option value="delivered">Delivered</option>
                </select>
                <button class="rounded-lg bg-orange-500 px-3 py-2 text-sm font-bold text-white hover:bg-orange-400">Apply</button>
            </div>
        </div>
        <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-12 px-4 py-3"></th><th class="px-4 py-3">Order</th><th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Status</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3">Transaction ID</th>
                        <th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Placed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        @php($transaction = $order->latestPaymentTransaction)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-4 py-4"><input type="checkbox" name="order_ids[]" value="{{ $order->id }}" data-order-checkbox class="h-4 w-4 rounded border-slate-300"></td>
                            <td class="px-4 py-4 font-semibold"><a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-700 hover:underline">{{ $order->order_number }}</a></td>
                            <td class="px-4 py-4">{{ $order->customer_email ?: 'Guest' }}</td>
                            <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : ($order->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">{{ $order->status === 'confirmed' && $order->isPaid() ? 'Paid' : Str::headline($order->status) }}</span></td>
                            <td class="px-4 py-4 font-medium">{{ Str::headline($order->payment_method ?: '—') }}</td>
                            <td class="px-4 py-4 font-mono text-xs text-slate-600">{{ $transaction?->manual_transaction_id ?: $transaction?->gateway_reference ?: '—' }}</td>
                            <td class="px-4 py-4 text-right font-semibold">₹{{ number_format((float) $order->grand_total, 2) }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-500">{{ $order->placed_at?->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-10 text-center text-slate-500">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    </form>
</section>
@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-bulk-orders]');
    if (!root) return;
    const all = root.querySelector('[data-select-all]');
    const boxes = [...root.querySelectorAll('[data-order-checkbox]')];
    const count = root.querySelector('[data-selected-count]');
    const update = () => { count.textContent = `${boxes.filter(box => box.checked).length} selected`; };
    all.addEventListener('change', () => { boxes.forEach(box => { box.checked = all.checked; }); update(); });
    boxes.forEach(box => box.addEventListener('change', update));
})();
</script>
@endpush
@endsection
