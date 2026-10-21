@extends('layouts.admin')
@section('title', 'Digital products')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">Digital product stock</h1>
    <p class="mt-2 text-slate-500">Add one license or credential at a time. Keys are encrypted and assigned only after payment confirmation.</p>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.digital-products.licenses.store') }}" class="mt-8 grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        <select name="product_id" required class="rounded-xl border border-slate-300 px-4 py-3" data-license-product><option value="">Select digital product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>@endforeach</select>
        <select name="product_variant_id" class="rounded-xl border border-slate-300 px-4 py-3" data-license-variant disabled><option value="">Product-level key (no variant)</option>@foreach($products as $product)@foreach($product->variants as $variant)<option value="{{ $variant->id }}" data-product-id="{{ $product->id }}">{{ $product->name }} — {{ $variant->attributeValues->map(fn ($value) => $value->attribute->name.': '.$value->value)->implode(', ') }}</option>@endforeach @endforeach</select>
        <textarea name="secret" required rows="3" placeholder="License key or username/password" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
        <textarea name="instructions" rows="2" placeholder="Customer instructions (optional)" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
        <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Add license stock</button>
    </form>
    <div class="mt-8 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-6 py-3">Reference</th>
                    <th class="px-6 py-3">Product</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Order</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Downloads</th>
                    <th class="px-6 py-3">Support</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($licenses as $license)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $license->reference }}</td>
                        <td class="px-6 py-4">{{ $license->product?->name }}@if($license->variant)<span class="block text-xs text-slate-500">{{ $license->variant->attributeValues->map(fn ($value) => $value->attribute->name.': '.$value->value)->implode(', ') }}</span>@endif</td>
                        <td class="px-6 py-4">{{ ucfirst($license->status) }}</td>
                        <td class="px-6 py-4">{{ $license->order?->order_number ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $license->email_status ?? 'pending' }}</td>
                        <td class="px-6 py-4">{{ $license->download_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.digital-products.licenses.reveal', $license) }}" target="_blank" class="font-semibold text-indigo-700 hover:underline">Reveal</a>
                                @if($license->status !== 'revoked')
                                    <form method="POST" action="{{ route('admin.digital-products.licenses.revoke', $license) }}">
                                        @csrf
                                        <button class="font-semibold text-red-700" onclick="return confirm('Revoke this license?')">Revoke</button>
                                    </form>
                                @endif
                                @if(in_array($license->status, ['assigned', 'revoked'], true))
                                    <details>
                                        <summary class="cursor-pointer font-semibold text-amber-700">Reissue</summary>
                                        <form method="POST" action="{{ route('admin.digital-products.licenses.reissue', $license) }}" class="mt-2 grid gap-2">
                                            @csrf
                                            <textarea name="secret" required placeholder="Replacement credential" class="rounded border p-2"></textarea>
                                            <textarea name="instructions" placeholder="Instructions" class="rounded border p-2"></textarea>
                                            <button class="rounded bg-amber-600 px-2 py-1 font-semibold text-white">Save replacement</button>
                                        </form>
                                    </details>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">No digital license stock yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $licenses->links() }}</div>
</section>
<script>
document.querySelector('[data-license-product]')?.addEventListener('change', (event) => {
    const variant = document.querySelector('[data-license-variant]');
    const productId = event.target.value;
    variant.disabled = !productId;
    variant.value = '';
    [...variant.options].forEach((option) => {
        option.hidden = option.value !== '' && option.dataset.productId !== productId;
    });
});
</script>
@endsection
