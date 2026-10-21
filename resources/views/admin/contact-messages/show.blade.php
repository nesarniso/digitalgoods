@extends('layouts.admin')
@section('title', 'Contact message')
@section('content')
<section class="mx-auto max-w-4xl px-6 py-10 lg:px-10">
    <a href="{{ route('admin.contact-messages.index') }}" class="text-sm font-bold text-indigo-600">← Back to contact messages</a>
    <article class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6"><div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ $contactMessage->topic }}</p><h1 class="mt-2 text-3xl font-black">{{ $contactMessage->name }}</h1></div><time class="text-sm text-slate-500">{{ $contactMessage->created_at->format('d M Y, h:i A') }}</time></div>
        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2"><div><dt class="font-bold text-slate-500">Email</dt><dd class="mt-1"><a href="mailto:{{ $contactMessage->email }}" class="text-indigo-600 hover:underline">{{ $contactMessage->email }}</a></dd></div>@if($contactMessage->phone)<div><dt class="font-bold text-slate-500">Phone</dt><dd class="mt-1 text-slate-900">{{ $contactMessage->phone }}</dd></div>@endif @if($contactMessage->order_number)<div><dt class="font-bold text-slate-500">Order number</dt><dd class="mt-1 text-slate-900">{{ $contactMessage->order_number }}</dd></div>@endif</dl>
        <div class="mt-8 rounded-2xl bg-slate-50 p-5 text-sm leading-7 text-slate-700">{!! nl2br(e($contactMessage->message)) !!}</div>
        <div class="mt-8 flex flex-wrap gap-3"><a href="mailto:{{ $contactMessage->email }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white">Reply by email</a><form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button class="rounded-xl border border-red-200 px-5 py-3 text-sm font-bold text-red-600">Delete</button></form></div>
    </article>
</section>
@endsection
