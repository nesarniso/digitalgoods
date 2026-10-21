@extends('layouts.admin')

@section('title', 'Checkout abandonment')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Lead recovery</p>
            <h1 class="mt-2 text-3xl font-black">Checkout abandonment</h1>
            <p class="mt-2 text-slate-500">Customers who shared their contact details but did not complete checkout.</p>
        </div>
        <span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700">{{ $abandonedCarts->total() }} lead(s)</span>
    </div>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr><th class="px-6 py-4">Customer</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Email</th><th class="px-6 py-4">Items</th><th class="px-6 py-4">Captured</th><th class="px-6 py-4 text-right">Contact</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($abandonedCarts as $cart)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $cart->checkout_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $cart->checkout_phone }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $cart->checkout_email }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $cart->items_count }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ optional($cart->checkout_contacted_at)->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><a href="tel:{{ $cart->checkout_phone }}" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Call</a><a href="mailto:{{ $cart->checkout_email }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">Email</a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-slate-500">No checkout abandonment leads yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($abandonedCarts->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $abandonedCarts->links() }}</div>
        @endif
    </div>
</section>
@endsection
