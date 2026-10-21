@extends('layouts.admin')
@php($editing = $record->exists)
@php($resource = ['category' => 'categories', 'brand' => 'brands', 'coupon' => 'coupons'][$type] ?? $type.'s')
@section('title', Str::headline($editing ? 'Edit '.$type : 'Add '.$type))
@section('content')
<section class="mx-auto max-w-3xl px-6 py-10 lg:px-10">
    <a href="{{ route('admin.'.$resource) }}" class="text-sm font-semibold text-indigo-700">← Back</a>
    <h1 class="mt-5 text-3xl font-black">{{ $type === 'category' ? ($editing ? 'Edit '.Str::headline($categoryLevel) : 'Add '.Str::headline($categoryLevel)) : Str::headline($editing ? 'Edit '.$type : 'Add '.$type) }}</h1>
    <form method="POST" action="{{ $editing ? route('admin.'.$resource.'.update', $record) : route('admin.'.$resource.'.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf @if($editing) @method('PUT') @endif
        @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        @if($type === 'category')
            <label class="block text-sm font-semibold">Name<input name="name" required value="{{ old('name', $record->name) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
            @if($categoryLevel === 'category')
                <input type="hidden" name="parent_id" value="">
                <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">This is a top-level category.</p>
            @else
                <input type="hidden" name="level" value="{{ $categoryLevel }}">
                <label class="block text-sm font-semibold">Parent {{ $categoryLevel === 'subcategory' ? 'category' : 'subcategory' }}<select name="parent_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><option value="">Select a parent</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $record->parent_id) == $parent->id)>{{ $parent->name }}</option>@endforeach</select></label>
            @endif
            <div class="block text-sm font-semibold">
                <label for="icon">Category icon</label>
                <input id="icon" name="icon" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                <p class="mt-1 text-xs font-normal text-slate-500">JPG, PNG, or WebP. Maximum 2 MB.</p>
                @if($record->icon_path)
                    <div class="mt-3 flex items-center gap-3">
                        <img src="{{ asset('storage/media/'.$record->icon_path) }}" alt="Current {{ $record->name }} icon" class="h-12 w-12 rounded-full object-cover ring-1 ring-slate-200">
                        <label class="font-normal text-slate-600"><input type="checkbox" name="remove_icon" value="1"> Remove current icon</label>
                    </div>
                @endif
            </div>
            <label class="block text-sm font-semibold">Description<textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old('description', $record->description) }}</textarea></label>
            <div class="flex gap-6 text-sm"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->exists ? $record->is_active : true))> Active</label><label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $record->is_featured))> Featured</label></div>
        @elseif($type === 'brand')
            <label class="block text-sm font-semibold">Name<input name="name" required value="{{ old('name', $record->name) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
            <label class="block text-sm font-semibold">Website<input name="website" type="url" value="{{ old('website', $record->website) }}" placeholder="https://example.com" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
            <label class="block text-sm font-semibold">Description<textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old('description', $record->description) }}</textarea></label>
            <label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->exists ? $record->is_active : true))> Active</label>
        @else
            <label class="block text-sm font-semibold">Code<input name="code" required value="{{ old('code', $record->code) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal uppercase"></label>
            <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold">Type<select name="type" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">@foreach(['percentage','fixed','free_shipping'] as $option)<option value="{{ $option }}" @selected(old('type', $record->type ?: 'percentage') === $option)>{{ Str::headline($option) }}</option>@endforeach</select></label><label class="text-sm font-semibold">Value<input name="value" type="number" step="0.01" min="0" value="{{ old('value', $record->value) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label><label class="text-sm font-semibold">Minimum order<input name="min_order_amount" type="number" step="0.01" min="0" value="{{ old('min_order_amount', $record->min_order_amount) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label><label class="text-sm font-semibold">Usage limit<input name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $record->usage_limit) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label><label class="text-sm font-semibold">Starts at<input name="starts_at" type="datetime-local" value="{{ old('starts_at', $record->starts_at?->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label><label class="text-sm font-semibold">Expires at<input name="expires_at" type="datetime-local" value="{{ old('expires_at', $record->expires_at?->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label></div>
            <label class="block text-sm font-semibold">Description<textarea name="description" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old('description', $record->description) }}</textarea></label><label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->exists ? $record->is_active : true))> Active</label>
        @endif
        <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">{{ $editing ? 'Save changes' : 'Create' }}</button>
    </form>
</section>
@endsection
