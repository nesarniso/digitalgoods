@extends('layouts.admin')

@section('title', 'Withdrawals')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    @if(session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Finance</p>
    <h1 class="mt-2 text-3xl font-black">Entrepreneur withdrawals</h1>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Entrepreneur</th><th class="px-5 py-3">Amount</th><th class="px-5 py-3">Method</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($withdrawals as $withdrawal)
                <tr>
                    <td class="px-5 py-4">{{ $withdrawal->entrepreneur?->name }}<div class="text-xs text-slate-500">{{ $withdrawal->account_number }}</div></td>
                    <td class="px-5 py-4 font-semibold">৳{{ number_format((float) $withdrawal->amount, 2) }}</td>
                    <td class="px-5 py-4 uppercase">{{ $withdrawal->method }}</td>
                    <td class="px-5 py-4 capitalize">{{ $withdrawal->status }}</td>
                    <td class="px-5 py-4">
                        @if($withdrawal->status === 'pending')
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('admin.withdrawals.update', $withdrawal) }}">@csrf @method('PUT')<input type="hidden" name="status" value="approved"><button class="font-semibold text-emerald-600">Approve</button></form>
                                <form method="POST" action="{{ route('admin.withdrawals.update', $withdrawal) }}">@csrf @method('PUT')<input type="hidden" name="status" value="rejected"><button class="font-semibold text-red-600">Reject</button></form>
                            </div>
                        @else
                            <span class="text-slate-500">Processed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No withdrawal requests.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $withdrawals->links() }}</div>
</section>
@endsection
