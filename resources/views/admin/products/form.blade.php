@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('content')
<section class="product-editor mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-5">
        <div>
            <a href="{{ route('admin.catalog', $catalogQuery ?? []) }}" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 transition hover:gap-3">← Back to catalog</a>
            <div class="mt-4 flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-600 text-xl text-white shadow-lg shadow-indigo-200">✦</span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Catalog workspace</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $product->exists ? 'Edit product' : 'Create product' }}</h1>
                </div>
            </div>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">Build a polished product listing with clear pricing, rich content, and search-ready details.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</p>
            <p class="mt-1 flex items-center justify-end gap-2 text-sm font-bold text-slate-700"><span class="h-2 w-2 rounded-full bg-amber-400"></span>{{ Str::headline(old('status', $product->status ?: 'draft')) }}</p>
        </div>
    </div>
    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @if($product->exists) @method('PUT') @endif
        @foreach(($catalogQuery ?? []) as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        @if($errors->any())<div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm"><span class="text-lg">!</span><div><p class="font-bold">Please review the form</p><p class="mt-1">{{ $errors->first() }}</p></div></div>@endif
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-[0_12px_40px_-18px_rgba(15,23,42,0.25)] sm:p-8">
            <div class="flex items-start gap-4 border-b border-slate-100 pb-5"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-lg text-indigo-600">⌘</span><div><h2 class="text-xl font-black text-slate-950">Product information</h2><p class="mt-1 text-sm text-slate-500">Start with the identity and story customers will see.</p></div></div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="text-sm font-semibold sm:col-span-2">Name<input name="name" required value="{{ old('name', $product->name) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                <label class="text-sm font-semibold sm:col-span-2">URL slug<input name="slug" value="{{ old('slug', $product->slug) }}" placeholder="product-url-slug" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><span class="mt-1 block text-xs font-normal text-slate-500">Use lowercase words separated by hyphens.</span></label>
                <label class="text-sm font-semibold">SKU<input name="sku" required value="{{ old('sku', $product->sku) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                <label class="text-sm font-semibold">Type<select name="product_type" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">@foreach(['simple','variable','digital'] as $type)<option value="{{ $type }}" @selected(old('product_type', $product->product_type ?: 'simple') === $type)>{{ Str::headline($type) }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold sm:col-span-2">Digital download URL <span class="font-normal text-slate-400">(optional)</span><input type="url" name="download_url" value="{{ old('download_url', $product->download_url) }}" placeholder="https://example.com/download" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><span class="mt-1 block text-xs font-normal text-slate-500">HTTPS only. Customers can access this link after the order is delivered. Each digital product needs a URL, uploaded file, or enough license keys before checkout is available. @if($product->exists)<a href="#product-downloads-title" class="font-bold text-indigo-700 underline">Upload a file instead ↓</a>@else Save this product first, then edit it to upload files.@endif</span></label>
                <label class="text-sm font-semibold">Category<select name="category_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">Brand<select name="brand_id" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><option value="">No brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold sm:col-span-2">Short description<textarea name="short_description" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old('short_description', $product->short_description) }}</textarea></label>
                <label class="text-sm font-semibold sm:col-span-2">Description<textarea name="description" rows="5" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old('description', $product->description) }}</textarea></label>
            </div>
            @unless($product->exists)
                <div class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                    <h3 class="font-bold text-slate-900">Product attributes</h3>
                    <p class="mt-1 text-sm text-slate-500">Select values such as Color, Size, or Storage. Variable products will be created for every selected attribute combination.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @forelse($attributes as $attribute)
                            <fieldset class="rounded-xl border border-slate-200 bg-white p-3">
                                <legend class="px-1 text-sm font-bold">{{ $attribute->name }}</legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse($attribute->values as $value)
                                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs">
                                            <input type="checkbox" name="attribute_value_ids[]" value="{{ $value->id }}" @checked(in_array($value->id, old('attribute_value_ids', [])))>
                                            {{ $value->value }}
                                        </label>
                                    @empty
                                        <div class="w-full rounded-lg border border-dashed border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                            No values configured for {{ $attribute->name }}.
                                            <a href="{{ route('admin.attributes.edit', $attribute) }}" class="font-bold underline">Add values in Attributes</a>
                                        </div>
                                    @endforelse
                                </div>
                            </fieldset>
                        @empty
                            <p class="text-sm text-slate-500 sm:col-span-2">No attributes have been created yet. Add them from Admin → Attributes first.</p>
                        @endforelse
                    </div>
                </div>
            @endunless
            @unless($product->exists)
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h2 class="font-bold">Product gallery images</h2>
                    <p class="mt-1 text-sm text-slate-500">Upload gallery images now. You can add attribute-specific variation images after creating the product.</p>
                    <div class="mt-5 space-y-3">
                        <input type="file" name="create_images[]" multiple accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border border-slate-300 p-2.5 text-sm">
                        <input name="create_images_alt_text" maxlength="255" placeholder="Gallery image alt text for SEO" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                        <input name="create_images_title" maxlength="255" placeholder="Gallery image title (optional)" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="create_images_thumbnail" value="1"> Mark uploaded images as thumbnail</label>
                    </div>
                </div>
            @endunless
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold">Search engine optimization</h2>
                <p class="mt-1 text-sm text-slate-500">Customize how this product appears in search results and social previews.</p>
                <div class="mt-5 grid gap-4">
                    <label class="text-sm font-semibold">Meta title<input name="meta_title" maxlength="255" value="{{ old('meta_title', $product->meta_title) }}" placeholder="Product name | Store name" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                    <label class="text-sm font-semibold">Meta description<textarea name="meta_description" maxlength="320" rows="3" placeholder="A concise, search-friendly product summary." class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">{{ old('meta_description', $product->meta_description) }}</textarea></label>
                </div>
            </div>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-[0_12px_40px_-18px_rgba(15,23,42,0.25)] sm:p-8">
            <div class="flex items-start gap-4 border-b border-slate-100 pb-5"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-lg text-amber-600">₹</span><div><h2 class="text-xl font-black text-slate-950">Pricing & publishing</h2><p class="mt-1 text-sm text-slate-500">Set your commercial rules and decide how the product goes live.</p></div></div>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <label class="text-sm font-semibold">Production / purchase cost<input name="purchase_price" type="number" step="0.01" min="0" value="{{ old('purchase_price', $product->purchase_price) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><span class="mt-1 block text-xs font-normal text-slate-500">Optional. Add it later before making this product available to entrepreneurs.</span></label>
                <label class="text-sm font-semibold">Regular price<input name="regular_price" type="number" step="0.01" min="0" required value="{{ old('regular_price', $product->regular_price) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                <label class="text-sm font-semibold">Sale price<input name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                <label class="text-sm font-semibold">Tax rate %<input name="tax_rate" type="number" step="0.01" min="0" max="100" value="{{ old('tax_rate', $product->tax_rate ?: 0) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                <label class="text-sm font-semibold sm:col-span-2">Status<select name="status" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">@foreach(['draft','pending_review','published','rejected'] as $status)<option value="{{ $status }}" @selected(old('status', $product->status ?: 'draft') === $status)>{{ Str::headline($status) }}</option>@endforeach</select></label>
                <label class="flex items-center gap-2 self-end pb-3 text-sm font-semibold"><input name="featured" type="checkbox" value="1" @checked(old('featured', $product->featured))> Featured product</label>
            </div>
        </div>
        <div class="flex flex-col-reverse items-stretch justify-between gap-4 rounded-3xl border border-slate-200 bg-slate-950 p-5 shadow-xl shadow-slate-300/40 sm:flex-row sm:items-center sm:p-6">
            <p class="text-sm text-slate-300"><span class="font-bold text-white">Almost there.</span> Review the details before publishing.</p>
            <button class="rounded-xl bg-indigo-500 px-7 py-3.5 font-black text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5 hover:bg-indigo-400">{{ $product->exists ? 'Save changes' : 'Create product' }} <span class="ml-2">→</span></button>
        </div>
    </form>
    @if($product->exists)
        @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200" aria-labelledby="product-downloads-title">
                <h2 id="product-downloads-title" class="font-bold">Upload digital product files</h2>
                <p class="mt-1 text-sm text-slate-500">Customers can download private files from their account after the order is delivered and payment is complete. You can also use license keys from the Digital Products menu.</p>
                <div class="mt-4 space-y-3">
                    @forelse($product->downloadFiles as $downloadFile)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $downloadFile->original_name }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($downloadFile->file_size / 1048576, 2) }} MB</p>
                            </div>
                            <form method="POST" action="{{ route('admin.products.downloads.destroy', [$product, $downloadFile]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this download file?')" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white">Delete</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No private download files uploaded.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('admin.products.downloads.store', $product) }}" enctype="multipart/form-data" class="mt-5 space-y-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    @csrf
                    <label class="block text-sm font-semibold text-slate-700">Upload files
                        <input type="file" name="files[]" required multiple accept=".zip,.rar,.7z,.pdf,.mp4,.webm,.mov,.mp3,.epub,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.psd,.ai,.jpg,.jpeg,.png,.webp" class="mt-2 w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
                    </label>
                    <p class="text-xs text-slate-500">Up to 10 files per upload, maximum 1 GB per file. Files are private and available from the customer dashboard after the order is delivered.</p>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">Upload download files</button>
                </form>
            </section>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold">Product gallery images</h2>
                <p class="mt-1 text-sm text-slate-500">Manage all product images from the Gallery menu in the sidebar.</p>
                <div class="mt-4 grid grid-cols-3 gap-3">@forelse($product->images as $image)<div class="group relative"><img src="{{ $image->url }}" alt="{{ $image->alt_text ?: $product->name }}" title="{{ $image->title }}" class="aspect-square rounded-xl object-cover ring-1 ring-slate-200"><form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" class="absolute right-1 top-1">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Delete this image?')" class="rounded-lg bg-red-600 px-2 py-1 text-xs font-bold text-white shadow">Delete</button></form><p class="mt-1 truncate text-[10px] text-slate-500">{{ $image->alt_text ?: 'No alt text' }}</p></div>@empty<p class="col-span-3 text-sm text-slate-500">No general gallery images uploaded.</p>@endforelse</div>
                <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-5 space-y-3">@csrf<div data-image-preview class="grid grid-cols-3 gap-2 empty:hidden"></div><input type="file" name="images[]" required multiple accept="image/*" data-image-input class="w-full rounded-xl border border-slate-300 p-2 text-sm"><input name="alt_text" maxlength="255" placeholder="Gallery image alt text for SEO" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><input name="title" maxlength="255" placeholder="Gallery image title (optional)" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_thumbnail" value="1"> Use uploaded images as thumbnail</label><button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Upload gallery images</button></form>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold">Variants</h2>
                <div class="mt-4 space-y-3">@forelse($product->variants as $variant)<div class="rounded-xl bg-slate-50 px-4 py-3 text-sm"><div class="flex items-center justify-between gap-3"><span><strong>{{ $variant->sku }}</strong> @if($variant->attributeValues->isNotEmpty())<small class="text-indigo-600">({{ $variant->attributeValues->pluck('value')->join(' / ') }})</small>@endif @if($variant->is_default)<small class="text-indigo-600">Default</small>@endif</span><div class="flex items-center gap-2"><strong>₹{{ number_format((float) $variant->effective_price, 2) }}</strong><form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Delete this variant?')" class="rounded-lg bg-red-600 px-2 py-1 text-xs font-bold text-white">Delete</button></form></div></div><form method="POST" action="{{ route('admin.products.variants.update', [$product, $variant]) }}" class="mt-3 grid gap-2 sm:grid-cols-2">@csrf @method('PUT')<input name="sku" required value="{{ $variant->sku }}" placeholder="SKU" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><input name="barcode" value="{{ $variant->barcode }}" placeholder="Barcode" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><input name="price" required type="number" step="0.01" value="{{ $variant->price }}" placeholder="Price" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><input name="sale_price" type="number" step="0.01" value="{{ $variant->sale_price }}" placeholder="Sale price" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><input name="purchase_price" type="number" step="0.01" value="{{ $variant->purchase_price }}" placeholder="Purchase cost" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><input name="weight" type="number" step="0.001" value="{{ $variant->weight }}" placeholder="Weight" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs">@foreach($attributes as $attribute)<label class="text-xs font-semibold">{{ $attribute->name }}<select name="attribute_value_ids[]" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><option value="">No {{ strtolower($attribute->name) }}</option>@foreach($attribute->values as $value)<option value="{{ $value->id }}" @selected($variant->attributeValues->contains('id', $value->id))>{{ $value->value }}</option>@endforeach</select></label>@endforeach<label class="flex items-center gap-2 text-xs"><input type="checkbox" name="is_default" value="1" @checked($variant->is_default)> Default variant</label><button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white sm:col-span-2">Save variant changes</button></form><div class="mt-3 grid grid-cols-3 gap-2">@forelse($variant->images as $image)<div class="relative"><img src="{{ $image->url }}" alt="{{ $image->alt_text ?: $product->name.' '.$variant->sku }}" class="aspect-square rounded-lg object-cover"><form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" class="absolute right-1 top-1">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Delete this image?')" class="rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">Delete</button></form></div>@empty<p class="col-span-3 text-xs text-slate-500">No variation images.</p>@endforelse</div><form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2">@csrf<input type="hidden" name="product_variant_id" value="{{ $variant->id }}"><input type="file" name="images[]" required multiple accept="image/*" class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white p-1.5 text-xs"><input name="alt_text" maxlength="255" placeholder="Variation image alt text" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs"><button class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Upload for this variation</button></form></div>@empty<p class="text-sm text-slate-500">No variants added yet. Add attributes and a variant below first.</p>@endforelse</div>
                <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="mt-5 grid gap-3 sm:grid-cols-2">@csrf<p class="sm:col-span-2 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-800">Each variation may combine one value from every attribute, for example <strong>Color: Black + Size: XL + Storage: 128GB</strong>. Every combination keeps its own price and stock.</p><input name="sku" placeholder="Variant SKU (optional; auto-generated)" class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><input name="barcode" placeholder="Barcode (optional)" class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><input name="purchase_price" type="number" step="0.01" min="0" placeholder="Purchase cost" class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><input name="price" required type="number" step="0.01" min="0" placeholder="Regular price" class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><input name="sale_price" type="number" step="0.01" min="0" placeholder="Sale price" class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><input name="weight" type="number" step="0.001" min="0" placeholder="Weight" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">@foreach($attributes as $attribute)<label class="text-sm font-semibold">{{ $attribute->name }}<select name="attribute_value_ids[]" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">No {{ strtolower($attribute->name) }}</option>@foreach($attribute->values as $value)<option value="{{ $value->id }}">{{ $value->value }}</option>@endforeach</select></label>@endforeach<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> Default variant</label><button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Add variant with attributes</button></form>
            </div>
        </div>
        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200" aria-labelledby="product-videos-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="product-videos-title" class="font-bold">Product preview videos</h2>
                    <p class="mt-1 text-sm text-slate-500">Review the videos shown on this product and its preview feed. {{ $product->videos->count() }} video(s).</p>
                </div>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($product->videos as $video)
                    <article class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-slate-800" title="{{ $video->title }}">{{ $video->title ?: 'Untitled preview' }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ strtoupper(str_replace('video/', '', $video->mime_type)) }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.products.videos.destroy', [$product, $video]) }}" class="shrink-0">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this product video?')" class="rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-red-700">Delete</button>
                            </form>
                        </div>
                        <video controls playsinline preload="metadata" class="mt-3 aspect-[9/16] max-h-[420px] w-full rounded-lg bg-slate-950 object-contain">
                            <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                            Your browser does not support video playback.
                        </video>
                    </article>
                @empty
                    <p class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">No product preview videos yet.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('admin.products.videos.store', $product) }}" enctype="multipart/form-data" class="mt-6 grid gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                @csrf
                <label class="text-sm font-semibold text-slate-700">Video file
                    <input type="file" name="video" required accept="video/mp4,video/webm,.mp4,.webm" class="mt-2 w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
                </label>
                <label class="text-sm font-semibold text-slate-700">Title <span class="font-normal text-slate-400">(optional)</span>
                    <input type="text" name="title" maxlength="255" value="{{ old('title') }}" placeholder="Sample video title" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-normal">
                </label>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Upload video</button>
                <p class="text-xs text-slate-500 sm:col-span-3">MP4 or WebM · Maximum file size: 100 MB</p>
            </form>
        </section>
    @endif
</section>
<script>
document.querySelectorAll('[data-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const preview = input.form.querySelector('[data-image-preview]');
        preview.replaceChildren();
        Array.from(input.files).forEach((file) => {
            if (!file.type.startsWith('image/')) return;
            const image = document.createElement('img');
            image.className = 'aspect-square rounded-lg object-cover ring-1 ring-indigo-200';
            image.alt = file.name;
            image.src = URL.createObjectURL(file);
            preview.appendChild(image);
        });
    });
});
</script>
@endsection
