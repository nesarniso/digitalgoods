@extends('layouts.admin')
@section('title', 'Marketing campaigns')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div class="mb-8">
        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">Marketing</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Marketing campaigns</h1>
        <p class="mt-2 text-slate-500">Create beautiful HTML campaigns and send them to your subscriber list.</p>
    </div>
    @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <section class="mb-6 rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div><p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-300">Contact manager</p><h2 class="mt-2 text-2xl font-black">Import reusable contacts</h2><p class="mt-1 text-sm text-slate-300">Upload once; large CSV files are automatically split into 1,000-contact lists.</p></div>
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.marketing.contact-lists.import') }}" class="flex flex-col gap-2 sm:min-w-[360px]">
                @csrf
                <input name="list_name" required placeholder="List name, e.g. September subscribers" class="rounded-xl border-0 px-4 py-3 text-sm text-slate-900">
                <div class="flex gap-2"><input type="file" name="contact_csv" required accept=".csv,text/csv" class="min-w-0 flex-1 rounded-xl bg-white/10 px-3 py-2 text-xs"><button class="rounded-xl bg-indigo-500 px-4 py-2 text-xs font-extrabold">Import CSV</button></div>
            </form>
        </div>
        @if($contactLists->isNotEmpty())
            <div class="mt-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">@foreach($contactLists as $list)<div class="rounded-xl bg-white/10 px-4 py-3"><p class="font-bold">{{ $list->name }}</p><p class="mt-1 text-xs text-slate-300">{{ number_format($list->contact_count) }} contacts ready</p></div>@endforeach</div>
        @endif
    </section>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.marketing.campaigns.store') }}" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
        @csrf
        <h2 class="text-xl font-extrabold text-slate-900">Create campaign</h2>
        <div class="mt-6 grid gap-4">
            <input name="name" required value="{{ old('name', 'Welcome campaign') }}" placeholder="Campaign name" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                <label class="block text-xs font-extrabold uppercase tracking-wide text-indigo-700">Email template</label>
                <select id="campaign-template" class="mt-2 w-full rounded-xl border border-indigo-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    <option value="">Start with a blank campaign email</option>
                    @foreach($emailTemplates as $emailTemplate)
                        <option value="{{ $emailTemplate->id }}" data-subject="{{ $emailTemplate->subject }}" data-body="{{ base64_encode($emailTemplate->body) }}">{{ $emailTemplate->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-indigo-700">Email channel only. Select a saved template, then customize it before sending.</p>
            </div>
            <input id="campaign-subject" name="subject" required value="{{ old('subject', 'A special message for you') }}" placeholder="Email subject" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            <label class="text-sm font-bold text-slate-700">HTML email content
                <textarea id="campaign-body" name="body" required rows="16" class="mt-2 block min-h-[360px] w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs leading-5 outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100">{{ old('body', $defaultBody) }}</textarea>
            </label>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div><h3 class="text-base font-extrabold text-slate-900">Choose audience</h3><p class="mt-1 text-xs text-slate-500">Select saved customers, all subscribers, or import a CSV list.</p></div>
                    <label class="flex items-center gap-2 text-sm font-bold text-indigo-700"><input id="all-customers" type="checkbox" name="all_customers" value="1" @checked(old('all_customers')) class="h-4 w-4 rounded border-slate-300 text-indigo-600"> All customers ({{ $customers->count() }})</label>
                </div>
                @if($contactLists->isNotEmpty())
                    <div class="mt-4 rounded-xl bg-indigo-50 p-4"><p class="text-xs font-extrabold uppercase tracking-wide text-indigo-700">Saved contact lists</p><div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($contactLists as $list)<label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="contact_list_ids[]" value="{{ $list->id }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600"><span>{{ $list->name }} <small class="text-slate-400">({{ number_format($list->contact_count) }})</small></span></label>@endforeach</div></div>
                @endif
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
                        <div class="mb-3 flex items-center justify-between"><p class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Customer list</p><button type="button" id="select-all-customers" class="text-xs font-bold text-indigo-600">Select all</button></div>
                        <div class="max-h-56 space-y-2 overflow-y-auto pr-2">
                            @forelse($customers as $customer)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 text-sm hover:bg-indigo-50">
                                    <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" @checked(in_array($customer->id, old('customer_ids', []))) class="customer-checkbox h-4 w-4 rounded border-slate-300 text-indigo-600">
                                    <span class="min-w-0"><span class="block truncate font-semibold text-slate-700">{{ $customer->name ?: 'Customer' }}</span><span class="block truncate text-xs text-slate-400">{{ $customer->email }}</span></span>
                                </label>
                            @empty
                                <p class="py-5 text-center text-xs text-slate-400">No customer email addresses found.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="grid gap-4">
                        <label class="rounded-xl bg-white p-4 ring-1 ring-slate-200"><span class="text-xs font-extrabold uppercase tracking-wide text-slate-500">External subscriber CSV</span><span class="mt-1 block text-xs text-slate-400">CSV columns may include email and name.</span><input type="file" name="subscriber_csv" accept=".csv,text/csv" class="mt-3 block w-full rounded-lg border border-slate-200 px-3 py-2 text-xs"></label>
                        <label class="rounded-xl bg-white p-4 ring-1 ring-slate-200"><span class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Manual email addresses</span><textarea name="recipients" rows="4" placeholder="parent@example.com&#10;student@example.com, another@example.com" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500">{{ old('recipients') }}</textarea><span class="mt-1 block text-xs text-slate-400">One per line or comma/semicolon separated.</span></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-700">Queue campaign</button>
            <button type="button" onclick="previewCampaign()" class="rounded-xl border border-indigo-200 px-5 py-3 text-sm font-extrabold text-indigo-700 hover:bg-indigo-50">Preview</button>
        </div>
    </form>
    <div class="mt-8 grid gap-3">
        @foreach($campaigns as $campaign)
            <div class="flex flex-col justify-between gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center">
                <div><p class="font-bold text-slate-900">{{ $campaign->name }}</p><p class="text-xs text-slate-500">{{ $campaign->subject }} · {{ $campaign->sent_count }}/{{ count($campaign->recipients ?? []) }} sent</p>@if($campaign->error_message)<p class="mt-1 max-w-2xl text-xs text-red-600">{{ $campaign->error_message }}</p>@endif</div>
                <span class="w-fit rounded-full px-3 py-1 text-xs font-bold uppercase {{ $campaign->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : ($campaign->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ $campaign->status }}</span>
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $campaigns->links() }}</div>
</section>
<dialog id="campaign-preview" class="w-[min(92vw,700px)] rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/50">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><strong>Campaign preview</strong><button type="button" onclick="this.closest('dialog').close()" class="text-slate-500">Close</button></div>
    <div class="p-5"><iframe id="campaign-preview-frame" title="Campaign preview" class="h-[520px] w-full rounded-xl border border-slate-200 bg-white"></iframe></div>
</dialog>
<script>
    document.getElementById('select-all-customers')?.addEventListener('click', () => {
        document.querySelectorAll('.customer-checkbox').forEach((checkbox) => checkbox.checked = true);
    });
    function previewCampaign() {
        const frame = document.getElementById('campaign-preview-frame');
        frame.srcdoc = document.getElementById('campaign-body').value;
        document.getElementById('campaign-preview').showModal();
    }
</script>
<script>
    document.getElementById('campaign-template')?.addEventListener('change', function () {
        const option = this.selectedOptions[0];
        if (!option?.value) return;
        document.getElementById('campaign-subject').value = option.dataset.subject || '';
        document.getElementById('campaign-body').value = option.dataset.body
            ? decodeURIComponent(escape(window.atob(option.dataset.body)))
            : '';
    });
</script>
@endsection
