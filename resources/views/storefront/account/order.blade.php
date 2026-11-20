@extends('layouts.storefront')
@section('title', 'Order '.$order->order_number.' | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6"><a href="{{ route('account.index') }}" class="text-sm font-semibold text-indigo-700">← Back to account</a><div class="mt-5 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Order tracking</p><h1 class="mt-2 text-3xl font-black">{{ $order->order_number }}</h1><p class="mt-2 text-slate-500">{{ $order->placed_at?->format('d M Y, h:i A') }}</p></div><span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700">{{ Str::headline($order->status) }}</span></div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-xl font-black">Shipping status</h2>
                @php($shipment = $order->latestShipment)
                @if($shipment)
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Courier</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ Str::headline($shipment->courier) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ Str::headline(str_replace('_', ' ', $shipment->status)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tracking number</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ $shipment->tracking_number ?: 'Not assigned yet' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Delivery fee</p>
                            <p class="mt-1 font-semibold text-slate-800">₹{{ number_format((float) $shipment->delivery_fee, 2) }}</p>
                        </div>
                    </div>
                    @if($shipment->tracking_url)
                        <a href="{{ $shipment->tracking_url }}" target="_blank" rel="noreferrer" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Track shipment</a>
                    @endif
                @else
                    <p class="mt-4 text-sm text-slate-500">Shipment information will appear here once the order is packed and dispatched.</p>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h2 class="text-xl font-black">Tracking timeline</h2><div class="mt-6 space-y-5">@foreach($order->statusHistory as $history)<div class="flex gap-4"><div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-indigo-600"></div><div><p class="font-bold">{{ Str::headline($history->status) }}</p><p class="text-sm text-slate-500">{{ $history->message ?: 'Order status updated' }} · {{ $history->created_at?->format('d M Y, h:i A') }}</p></div></div>@endforeach</div></div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h2 class="text-xl font-black">Items</h2><div class="mt-4 divide-y divide-slate-100">@foreach($order->items as $item)<div class="flex flex-wrap justify-between gap-4 py-4"><div><p class="font-semibold">@if($item->product)<a href="{{ route('products.show', $item->product->slug) }}" class="text-slate-900 underline decoration-transparent underline-offset-4 transition hover:text-indigo-700 hover:decoration-indigo-500">{{ $item->product_name }} <span class="text-xs font-medium text-indigo-600">View product</span></a>@else{{ $item->product_name }}@endif</p>@if($item->variant_label)<p class="mt-1 text-sm font-medium text-indigo-600">{{ $item->variant_label }}</p>@elseif($item->variant)<p class="mt-1 text-sm font-medium text-indigo-600">{{ $item->variant->attributeValues->map(fn ($value) => "{$value->attribute->name}: {$value->value}")->implode(', ') }}</p>@endif<p class="text-sm text-slate-500">Qty: {{ $item->quantity }}</p></div><p class="font-bold">₹{{ number_format((float) $item->line_total, 2) }}</p></div>@endforeach</div></div>
            @if($digitalLicenses->isNotEmpty())<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm"><h2 class="text-xl font-black text-emerald-900">Digital product key</h2><p class="mt-2 text-sm text-emerald-800">This key is visible only inside your authenticated customer account.</p><div class="mt-4 space-y-3">@foreach($digitalLicenses as $license)<div class="rounded-xl bg-white p-4 ring-1 ring-emerald-200"><p class="font-bold text-slate-900">{{ $license['product_name'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $license['reference'] }}</p><p class="mt-3 break-all rounded-lg bg-slate-900 p-3 font-mono text-sm text-white">{{ $license['secret'] }}</p>@if($license['instructions'])<p class="mt-3 whitespace-pre-wrap text-sm text-slate-600">{{ $license['instructions'] }}</p>@endif</div>@endforeach</div></div>@endif
            @if($digitalDeliveries->isNotEmpty())
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm">
                    <h2 class="text-xl font-black text-indigo-900">Your digital downloads</h2>
                    <p class="mt-2 text-sm text-indigo-800">Downloads are available here and on your account dashboard after the order is delivered and payment is complete.</p>
                    <div class="mt-4 space-y-3">
                        @foreach($digitalDeliveries as $delivery)
                            <a href="{{ $delivery['url'] }}" @if($delivery['external']) target="_blank" rel="noopener noreferrer" @endif class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 font-semibold text-indigo-700 ring-1 ring-indigo-100 hover:bg-indigo-100">
                                <span><span class="block text-xs font-medium text-slate-500">{{ $delivery['product_name'] }}</span>{{ $delivery['file_name'] }}</span>
                                <span aria-hidden="true">↓</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($order->isPaid() && $order->status === 'delivered' && $digitalDeliveries->isEmpty() && $digitalLicenses->isEmpty() && $order->items->contains(fn ($item) => $item->product?->product_type === 'digital'))
                <div role="status" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                    Digital delivery is being prepared for this order. Please contact <a href="{{ route('contact-us') }}" class="font-bold underline">customer support</a> if you need help.
                </div>
            @endif
            @if($order->returnRequests->isNotEmpty())<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h2 class="text-xl font-black">Return requests</h2><div class="mt-4 space-y-3">@foreach($order->returnRequests as $return)<div class="flex flex-wrap justify-between gap-3 rounded-xl bg-slate-50 p-4 text-sm"><div><p class="font-bold">{{ $return->request_number }}</p><p class="text-slate-500">{{ Str::headline(str_replace('_', ' ', $return->reason)) }} · Qty {{ $return->quantity }}</p></div><span class="font-semibold text-indigo-700">{{ Str::headline($return->status) }}</span></div>@endforeach</div></div>@endif
        </div>
        @php($paymentTransaction = $order->latestPaymentTransaction)
        @php($paymentStatus = $paymentTransaction?->isSuccessful() ? 'Paid' : (in_array($paymentTransaction?->status, ['pending', 'initiated'], true) ? 'Pending' : ($paymentTransaction ? Str::headline($paymentTransaction->status) : 'Pending')))
        <aside class="space-y-6"><div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h2 class="font-black">Summary</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">Payment method</dt><dd>{{ Str::headline($order->payment_method) }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Payment status</dt><dd class="font-semibold {{ $paymentStatus === 'Paid' ? 'text-emerald-600' : ($paymentStatus === 'Failed' ? 'text-red-600' : 'text-amber-600') }}">{{ $paymentStatus }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>₹{{ number_format((float) $order->subtotal, 2) }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Shipping</dt><dd>₹{{ number_format((float) $order->shipping_amount, 2) }}</dd></div><div class="flex justify-between border-t border-slate-200 pt-3 font-black"><dt>Total</dt><dd>₹{{ number_format((float) $order->grand_total, 2) }}</dd></div></dl></div></aside>
    </div>
</section>
@endsection
