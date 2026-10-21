@extends('layouts.entrepreneur')

@section('title', 'My Customers')
@section('header_title', 'My Customers')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Customer management</p>
    <h1 class="mt-2 text-3xl font-black">My Customers</h1>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Phone</th><th class="px-5 py-3">Orders</th><th class="px-5 py-3">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($customers as $customer)
                <tr><td class="px-5 py-4 font-semibold">{{ $customer->name }}</td><td class="px-5 py-4">{{ $customer->phone ?: $customer->email ?: '—' }}</td><td class="px-5 py-4">{{ $customer->entrepreneur_orders_count }}</td><td class="px-5 py-4"><a class="font-semibold text-[var(--brand-primary)] hover:underline" href="{{ route('entrepreneur.customers.orders', $customer) }}">View orders</a></td></tr>
            @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No customers from your sales yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $customers->links() }}</div>
</main>
@endsection
