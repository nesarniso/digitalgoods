@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <a href="{{ route('admin.orders') }}" class="text-sm font-semibold text-indigo-700">← Back to orders</a>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if(session('error') || $errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('error') ?: $errors->first() }}</div>@endif
    @if($order->payment_review_required)
        <div class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <div><p class="font-bold">Digital key assignment pending</p><p class="mt-1">{{ $order->payment_review_reason ?: 'One or more digital product keys still need to be assigned.' }}</p></div>
            @if($order->isPaid() && $order->status !== 'delivered')
                <form method="POST" action="{{ route('admin.orders.digital-licenses.retry', $order) }}">@csrf<button class="rounded-xl bg-amber-600 px-4 py-2 font-bold text-white">Retry key assignment</button></form>
            @endif
        </div>
    @endif
    @php($orderStatuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered'])
    <div class="mt-5 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Order details</p><h1 class="mt-2 text-3xl font-black">{{ $order->order_number }}</h1><p class="mt-2 text-slate-500">{{ $order->customer_email ?: 'Guest customer' }} · {{ $order->placed_at?->format('d M Y, H:i') }}</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700">Generate receipt</a><form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-2">@csrf @method('PUT')<select name="status" class="rounded-xl border border-slate-300 px-3 py-2" @disabled($order->status === 'delivered')>@foreach($orderStatuses as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ $status === 'confirmed' && $order->isPaid() ? 'Paid' : Str::headline($status) }}</option>@endforeach</select><button class="rounded-xl bg-indigo-600 px-4 py-2 font-semibold text-white" @disabled($order->status === 'delivered')>Update status</button></form></div></div>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_340px]">
        <div class="space-y-8">
            @php($customerAddress = $order->addresses->firstWhere('type', 'shipping') ?? $order->addresses->first())
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <h2 class="border-b border-slate-200 px-6 py-4 font-bold">Customer details</h2>
                <dl class="grid gap-5 px-6 py-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Name</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $customerAddress?->name ?: $order->user?->name ?: 'Guest customer' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Phone</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $customerAddress?->phone ?: $order->customer_phone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Email</dt>
                        <dd class="mt-1 break-all font-semibold text-slate-800">{{ $order->customer_email ?: $order->user?->email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Order number</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $order->order_number }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Delivery address</dt>
                        <dd class="mt-1 leading-6 text-slate-800">
                            {{ collect([$customerAddress?->address_line, $customerAddress?->area, $customerAddress?->upazila, $customerAddress?->district, $customerAddress?->division])->filter()->implode(', ') ?: 'No delivery address available' }}
                        </dd>
                    </div>
                </dl>
            </div>
            @php($paymentTransaction = $order->latestPaymentTransaction)
            @if($paymentTransaction)
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                        <h2 class="font-bold">Payment details</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $paymentTransaction->isSuccessful() ? 'bg-emerald-100 text-emerald-700' : ($paymentTransaction->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $paymentTransaction->isSuccessful() ? 'Paid' : (in_array($paymentTransaction->status, ['pending', 'initiated'], true) ? 'Pending' : Str::headline($paymentTransaction->status)) }}</span>
                    </div>
                    <dl class="grid gap-5 px-6 py-5 text-sm sm:grid-cols-2">
                        <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Method</dt><dd class="mt-1 font-semibold">{{ Str::headline($paymentTransaction->gateway) }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Amount</dt><dd class="mt-1 font-semibold">৳{{ number_format((float) $paymentTransaction->amount, 2) }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Payer mobile</dt><dd class="mt-1 font-semibold">{{ $paymentTransaction->payer_mobile ?: '—' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Transaction ID</dt><dd class="mt-1 break-all font-semibold">{{ $paymentTransaction->manual_transaction_id ?: '—' }}</dd></div>
                        @if($paymentTransaction->submitted_amount)
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Submitted amount</dt><dd class="mt-1 font-semibold">৳{{ number_format((float) $paymentTransaction->submitted_amount, 2) }}</dd></div>
                        @endif
                    </dl>
                    @if(in_array($paymentTransaction->status, ['pending', 'initiated'], true))
                        <div class="flex flex-wrap gap-3 border-t border-slate-200 px-6 py-4">
                            <form method="POST" action="{{ route('admin.payments.confirm', $paymentTransaction) }}" onsubmit="return confirm('Confirm this payment and mark the order as paid?')">
                                @csrf
                                <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Confirm payment</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.reject', $paymentTransaction) }}" class="flex flex-1 gap-2 sm:max-w-md">
                                @csrf
                                <input name="reason" required maxlength="500" placeholder="Rejection reason" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white">Reject</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <h2 class="border-b border-slate-200 px-6 py-4 font-bold">Items</h2>
                <div class="divide-y divide-slate-100">@foreach($order->items as $item)<div class="flex justify-between px-6 py-5"><div><p class="font-semibold">{{ $item->product_name }}</p>@if($item->variant_label)<p class="mt-1 text-sm font-medium text-indigo-600">{{ $item->variant_label }}</p>@elseif($item->variant)<p class="mt-1 text-sm font-medium text-indigo-600">{{ $item->variant->attributeValues->map(fn ($value) => "{$value->attribute->name}: {$value->value}")->implode(', ') }}</p>@endif<p class="text-sm text-slate-500">{{ $item->quantity }} × ৳{{ number_format((float) $item->unit_price, 2) }}</p></div><p class="font-bold">৳{{ number_format((float) $item->line_total, 2) }}</p></div>@endforeach</div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <h2 class="border-b border-slate-200 px-6 py-4 font-bold">Fulfilment</h2>
                <div class="space-y-5 px-6 py-5">
                    @if ($shipment)
                        <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $shipment->shipment_number }}</p>
                                    <p class="mt-1">Courier: {{ Str::headline($shipment->courier) }} · Tracking: {{ $shipment->tracking_number ?: 'N/A' }}</p>
                                </div>
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-700">{{ Str::headline(str_replace('_', ' ', $shipment->status)) }}</span>
                            </div>
                            @if($order->returnRequests->isNotEmpty())
                                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                                    <h2 class="border-b border-slate-200 px-6 py-4 font-bold">Return requests</h2>
                                    <div class="divide-y divide-slate-100">
                                        @foreach($order->returnRequests as $return)
                                            <form method="POST" action="{{ route('admin.orders.returns.status', [$order, $return]) }}" class="space-y-3 px-6 py-5">@csrf @method('PUT')
                                                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-bold">{{ $return->request_number }} · {{ $return->item->product_name }}</p><p class="text-sm text-slate-500">Qty {{ $return->quantity }} · {{ Str::headline(str_replace('_', ' ', $return->reason)) }}</p><p class="mt-1 text-sm text-slate-600">{{ $return->customer_note ?: 'No customer note.' }}</p></div><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ Str::headline($return->status) }}</span></div>
                                                <div class="flex flex-wrap gap-2"><select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">@foreach(['approved','rejected','received','refunded','cancelled'] as $status)<option value="{{ $status }}" @selected($return->status === $status)>{{ Str::headline($status) }}</option>@endforeach</select><input name="admin_note" value="{{ $return->admin_note }}" placeholder="Admin note" class="min-w-48 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm"><button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Update return</button></div>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('admin.orders.shipments.status', [$order, $shipment]) }}" class="flex flex-wrap gap-3">
                            @csrf
                            @method('PUT')
                            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                @foreach(['pending_dispatch','picked_up','in_transit','out_for_delivery','delivered','failed','cancelled'] as $shipmentStatus)
                                    <option value="{{ $shipmentStatus }}" @selected($shipment->status === $shipmentStatus)>{{ Str::headline(str_replace('_', ' ', $shipmentStatus)) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Update shipment</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.orders.shipments.create', $order) }}" class="space-y-3">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Courier</label>
                                    <select name="courier" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Delivery fee</label>
                                    <input type="text" name="delivery_fee" value="0.00" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Notes</label>
                                    <input type="text" name="notes" placeholder="Optional notes" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                </div>
                            </div>
                            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Create shipment</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @php($paymentStatus = $paymentTransaction?->isSuccessful() ? 'Paid' : (in_array($paymentTransaction?->status, ['pending', 'initiated'], true) ? 'Pending' : ($paymentTransaction ? Str::headline($paymentTransaction->status) : 'Pending')))
        <aside class="h-fit rounded-2xl bg-slate-900 p-6 text-white"><p class="text-slate-300">Order total</p><p class="mt-2 text-3xl font-black">৳{{ number_format((float) $order->grand_total, 2) }}</p><dl class="mt-6 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-400">Payment</dt><dd>{{ Str::headline($order->payment_method) }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Payment status</dt><dd class="{{ $paymentStatus === 'Paid' ? 'text-emerald-300' : ($paymentStatus === 'Failed' ? 'text-red-300' : 'text-amber-300') }}">{{ $paymentStatus }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Shipping</dt><dd>{{ $order->shipping_method_name ?: '—' }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Status</dt><dd>{{ $order->status === 'confirmed' && $order->isPaid() ? 'Paid' : Str::headline($order->status) }}</dd></div></dl></aside>
    </div>
</section>
@endsection
