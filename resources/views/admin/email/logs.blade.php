@extends('layouts.admin')
@section('title', 'Email logs')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Communication center</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Email delivery logs</h1>
            <p class="mt-2 text-sm text-slate-500">Review transactional email delivery attempts and failures.</p>
        </div>
        <a href="{{ route('admin.email.templates') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:border-indigo-300 hover:text-indigo-700">Email templates</a>
    </div>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-4">Date</th><th class="px-5 py-4">Recipient</th><th class="px-5 py-4">Subject</th><th class="px-5 py-4">Template</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Error</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-5 py-4 text-slate-500">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ $log->recipient }}</td>
                            <td class="max-w-xs truncate px-5 py-4 text-slate-700">{{ $log->subject }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $log->template_key ?: '—' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($log->status) }}</span></td>
                            <td class="max-w-xs truncate px-5 py-4 text-xs text-red-600">{{ $log->error ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No email attempts have been logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>
</section>
@endsection
