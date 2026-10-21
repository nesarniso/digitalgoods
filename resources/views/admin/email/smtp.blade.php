@extends('layouts.admin')
@section('title', 'SMTP settings')
@section('content')
<section class="mx-auto max-w-4xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">SMTP server</h1>
    <p class="mt-2 text-slate-500">Configure the mail server used for transactional emails.</p>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.email.smtp.save') }}" class="mt-8 grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2">
        @csrf
        <label class="text-sm font-semibold">Mailer<select name="mailer" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><option value="smtp" @selected($smtp['mailer'] === 'smtp')>SMTP</option><option value="log" @selected($smtp['mailer'] === 'log')>Log (development)</option></select></label>
        <label class="text-sm font-semibold">Encryption<select name="encryption" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><option value="tls" @selected($smtp['encryption'] === 'tls')>TLS</option><option value="ssl" @selected($smtp['encryption'] === 'ssl')>SSL</option><option value="none" @selected($smtp['encryption'] === '')>None</option></select></label>
        <label class="text-sm font-semibold">SMTP host<input name="host" value="{{ old('host', $smtp['host']) }}" placeholder="smtp.gmail.com" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
        <label class="text-sm font-semibold">Port<input name="port" type="number" value="{{ old('port', $smtp['port']) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
        <label class="text-sm font-semibold">Username<input name="username" value="{{ old('username', $smtp['username']) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
        <label class="text-sm font-semibold">Password<input name="password" type="password" placeholder="Leave blank to keep current" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
        <label class="text-sm font-semibold">From email<input name="from_address" type="email" required value="{{ old('from_address', $smtp['from_address']) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
        <label class="text-sm font-semibold">From name<input name="from_name" required value="{{ old('from_name', $smtp['from_name']) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
        <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white sm:col-span-2">Save SMTP settings</button>
    </form>
    <section class="mt-7 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 sm:p-7">
        <div class="mb-5">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-slate-400">Testing</p>
            <h2 class="mt-2 text-xl font-black text-slate-900">Send a test email</h2>
            <p class="mt-1 text-sm text-slate-500">Save your SMTP settings first, then send a real message to confirm the connection.</p>
        </div>
        <form method="POST" action="{{ route('admin.email.smtp.test') }}" class="grid gap-4">
            @csrf
            <label class="text-sm font-bold text-slate-700">Recipient email
                <input name="recipient" type="email" required value="{{ old('recipient') }}" placeholder="admin@example.com" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </label>
            <label class="text-sm font-bold text-slate-700">Email subject
                <input name="subject" required value="{{ old('subject', config('app.name').' SMTP test email') }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </label>
            <label class="text-sm font-bold text-slate-700">Email content <span class="font-normal text-slate-400">(HTML supported)</span>
                <textarea name="content" required rows="8" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-mono text-xs leading-5 text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('content', '<div style="font-family:Arial,sans-serif;padding:24px;background:#f8fafc;color:#0f172a"><div style="max-width:600px;margin:auto;background:#fff;border-radius:16px;padding:32px;box-shadow:0 10px 25px rgba(15,23,42,.08)"><h2 style="margin-top:0;color:#4f46e5">'.config('app.name').' SMTP Test</h2><p>Hello,</p><p>This is a test email to confirm your SMTP configuration is working correctly.</p><p style="margin-bottom:0">Regards,<br>'.config('app.name').' Team</p></div></div>') }}</textarea>
            </label>
            <button class="w-fit rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">Send test mail</button>
        </form>
    </section>
</section>
@endsection
