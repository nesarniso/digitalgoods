@extends('layouts.admin')
@section('title', $attribute->exists ? 'Edit attribute' : 'Add attribute')
@section('content')
<section class="mx-auto max-w-3xl px-6 py-10 lg:px-10">
    <a href="{{ route('admin.attributes') }}" class="text-sm font-semibold text-indigo-700">← Back</a>
    <h1 class="mt-5 text-3xl font-black">{{ $attribute->exists ? 'Edit attribute' : 'Add attribute' }}</h1>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ $attribute->exists ? route('admin.attributes.update', $attribute) : route('admin.attributes.store') }}" class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf @if($attribute->exists) @method('PUT') @endif
        <label class="block text-sm font-semibold">Name<input name="name" required value="{{ old('name', $attribute->name) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal" placeholder="Color, Size, Storage"></label>
        <p class="mt-2 text-xs text-slate-500">Slug: {{ $attribute->slug ?: 'Generated automatically' }}</p>
        <button class="mt-5 rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">{{ $attribute->exists ? 'Save changes' : 'Create attribute' }}</button>
    </form>
    @if($attribute->exists)
        <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-black">Attribute values</h2>
            <form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}" class="mt-4 flex gap-3">@csrf<input name="value" required class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3" placeholder="Black, White, Medium"><button class="rounded-xl bg-slate-900 px-4 py-3 font-bold text-white">Add value</button></form>
            <div class="mt-5 flex flex-wrap gap-2">@forelse($attribute->values as $value)<span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-sm">{{ $value->value }}<form method="POST" action="{{ route('admin.attribute-values.delete', $value) }}">@csrf @method('DELETE')<button class="font-bold text-red-600" title="Delete">×</button></form></span>@empty<p class="text-sm text-slate-500">No values yet.</p>@endforelse</div>
        </div>
        <form method="POST" action="{{ route('admin.attributes.delete', $attribute) }}" class="mt-6" onsubmit="return confirm('Delete this attribute?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600">Delete attribute</button></form>
    @endif
</section>
@endsection
