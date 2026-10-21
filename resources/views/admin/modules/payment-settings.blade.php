@extends('layouts.admin')
@section('title', 'Payment methods')
@section('content')
<section class="mx-auto max-w-4xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">Payment methods</h1>
    <p class="mt-2 text-slate-500">Configure wallet numbers, bank details, instructions and QR codes for checkout payments.</p>
    @if(session('success'))
        <div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.settings.payments.save') }}" enctype="multipart/form-data" class="mt-8 space-y-5">
        @csrf
        @foreach(['cash_on_delivery' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'bank_transfer' => 'Bank Transfer'] as $key => $label)
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between gap-4"><h2 class="text-xl font-black">{{ $label }}</h2><label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="{{ $key }}_enabled" value="1" @checked(old($key.'_enabled', $paymentSettings[$key]['enabled']))> Enabled</label></div>
                @if($key === 'cash_on_delivery') </div> @continue @endif
                @if($key !== 'bank_transfer')
                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Payment number
                    <input name="{{ $key }}_merchant_number" value="{{ old($key.'_merchant_number', $paymentSettings[$key]['merchant_number']) }}" placeholder="{{ $label }} merchant number" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                </label>
                @else
                    @foreach(['bank_name' => 'Bank name', 'account_name' => 'Account name', 'account_number' => 'Account number', 'branch' => 'Branch', 'routing_number' => 'Routing number'] as $field => $fieldLabel)
                        <label class="mt-4 block text-sm font-semibold text-slate-700">{{ $fieldLabel }}<input name="{{ $key }}_{{ $field }}" value="{{ old($key.'_'.$field, $paymentSettings[$key][$field]) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                    @endforeach
                @endif
                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Account type
                    <select name="{{ $key }}_account_type" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                        @foreach($accountTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" @selected(old($key.'_account_type', $paymentSettings[$key]['account_type']) === $typeKey)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="mt-4 block text-sm font-semibold text-slate-700">QR code image
                    <input type="file" name="{{ $key }}_qr_code" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                    @if($paymentSettings[$key]['qr_code'])<img src="{{ asset('storage/media/'.$paymentSettings[$key]['qr_code']) }}" alt="{{ $label }} QR code" class="mt-3 h-32 w-32 rounded-lg object-contain ring-1 ring-slate-200">@endif
                </label>
                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Checkout instructions <span class="font-normal text-slate-500">(default Send Money instruction is saved if left blank)</span>
                    <textarea name="{{ $key }}_instructions" rows="4" placeholder="Send Money instruction" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old($key.'_instructions', $paymentSettings[$key]['instructions']) }}</textarea>
                </label>
            </div>
        @endforeach
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Save payment settings</button>
    </form>
</section>
@endsection
