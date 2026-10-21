@extends('layouts.admin')
@section('title', 'Gallery | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">Store media</p>
    <h1 class="mt-2 text-3xl font-black">Gallery</h1>
    <p class="mt-2 text-slate-500">All product images in one place. Cropped edits remain attached to their original product.</p>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($images as $image)
            <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <button type="button" class="block w-full cursor-pointer" data-gallery-open data-image-url="{{ $image->editor_url }}" data-image-name="{{ $image->product?->name }}" data-image-id="{{ $image->id }}" data-image-action="{{ route('admin.gallery.update', $image) }}" data-image-alt="{{ $image->alt_text }}" data-image-title="{{ $image->title }}" data-image-thumbnail="{{ $image->is_thumbnail ? '1' : '0' }}" aria-label="Edit image for {{ $image->product?->name }}">
                    <img src="{{ $image->url }}" alt="{{ $image->alt_text ?: $image->product?->name }}" class="aspect-square w-full object-cover">
                </button>
                <div class="p-4">
                    <p class="truncate text-sm font-bold text-slate-800">{{ $image->product?->name ?: 'Deleted product' }}</p>
                    @if($image->variant)<p class="mt-1 text-xs text-slate-500">Variant: {{ $image->variant->sku }}</p>@endif
                    <button type="button" data-gallery-open data-image-url="{{ $image->editor_url }}" data-image-name="{{ $image->product?->name }}" data-image-id="{{ $image->id }}" data-image-action="{{ route('admin.gallery.update', $image) }}" data-image-alt="{{ $image->alt_text }}" data-image-title="{{ $image->title }}" data-image-thumbnail="{{ $image->is_thumbnail ? '1' : '0' }}" class="mt-3 w-full rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white">Edit, crop & save</button>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" class="mt-2" onsubmit="return confirm('Remove this image from the product?')">
                        @csrf @method('DELETE')
                        <button class="w-full rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700">Remove image</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-xl bg-white p-8 text-sm text-slate-500 sm:col-span-2 lg:col-span-4">No product images found.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $images->links() }}</div>
</section>
<div id="gallery-editor" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/70 p-4" role="dialog" aria-modal="true" aria-labelledby="gallery-editor-title">
    <div class="w-full max-w-2xl overflow-y-auto overscroll-contain rounded-2xl bg-white p-5 shadow-2xl" style="max-height: calc(100vh - 2rem);" data-gallery-modal-content tabindex="0">
        <div class="flex shrink-0 items-center justify-between gap-4"><div><h2 id="gallery-editor-title" class="text-xl font-black">Edit product image</h2><p id="gallery-editor-product" class="text-sm text-slate-500"></p></div><button type="button" data-gallery-close class="rounded-lg px-3 py-2 text-xl text-slate-500">×</button></div>
        <div class="pr-1">
            <p class="mt-4 text-sm text-slate-600">ছবির উপর square box টেনে crop area select করুন। তৈরি হওয়া box-এর ভেতর ধরে সরানো যাবে।</p>
            <div class="mt-3 overflow-hidden rounded-xl bg-slate-900"><canvas id="gallery-canvas" class="mx-auto block max-h-[45vh] max-w-full touch-none"></canvas></div>
            <button type="button" id="gallery-new-selection" class="mt-3 rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">New selection</button>
        </div>
        <form id="gallery-editor-form" method="POST" enctype="multipart/form-data" class="sticky bottom-0 mt-4 space-y-3 border-t border-slate-200 bg-white pt-4">@csrf @method('PUT')
            <input id="gallery-editor-file" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-slate-300 p-2 text-sm">
            <input id="gallery-editor-alt" name="alt_text" maxlength="255" placeholder="Alt text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input id="gallery-editor-title-input" name="title" maxlength="255" placeholder="Image title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <label class="flex items-center gap-2 text-sm"><input id="gallery-editor-thumbnail" type="checkbox" name="is_thumbnail" value="1"> Use as thumbnail</label>
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white">Crop & save</button>
        </form>
    </div>
    </div>
</div>
<script>
(() => {
    const editor = document.getElementById('gallery-editor'), modalContent = document.querySelector('[data-gallery-modal-content]'), canvas = document.getElementById('gallery-canvas'), ctx = canvas.getContext('2d');
    const fileInput = document.getElementById('gallery-editor-file'), form = document.getElementById('gallery-editor-form'), source = new Image();
    let crop = { x: 0, y: 0, size: 0 }, dragging = false, moving = false, selecting = true, start = null, moveOffset = null;
    modalContent.addEventListener('wheel', (event) => {
        event.preventDefault();
        modalContent.scrollTop += event.deltaY;
    }, { passive: false });
    const draw = () => {
        if (!source.naturalWidth) return;
        const scale = Math.min(1, 560 / source.naturalWidth);
        canvas.width = Math.round(source.naturalWidth * scale); canvas.height = Math.round(source.naturalHeight * scale);
        ctx.drawImage(source, 0, 0, canvas.width, canvas.height);
        ctx.fillStyle = 'rgba(0,0,0,.45)'; ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.clearRect(crop.x, crop.y, crop.size, crop.size); ctx.drawImage(source, crop.x / scale, crop.y / scale, crop.size / scale, crop.size / scale, crop.x, crop.y, crop.size, crop.size);
        ctx.strokeStyle = '#fff'; ctx.lineWidth = 3; ctx.strokeRect(crop.x, crop.y, crop.size, crop.size);
    };
    const load = (url) => {
        source.onload = () => {
            const scale = Math.min(1, 560 / source.naturalWidth);
            const w = source.naturalWidth * scale, h = source.naturalHeight * scale;
            crop.size = 0;
            crop.x = 0;
            crop.y = 0;
            selecting = true;
            draw();
        };
        source.onerror = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#fff';
            ctx.font = '16px sans-serif';
            ctx.fillText('Image could not be loaded', 20, 30);
        };
        source.src = url;
    };
    document.querySelectorAll('[data-gallery-open]').forEach((button) => button.addEventListener('click', () => {
        editor.classList.remove('hidden'); editor.classList.add('flex'); document.body.classList.add('overflow-hidden'); form.action = button.dataset.imageAction;
        document.getElementById('gallery-editor-product').textContent = button.dataset.imageName;
        document.getElementById('gallery-editor-alt').value = button.dataset.imageAlt || '';
        document.getElementById('gallery-editor-title-input').value = button.dataset.imageTitle || '';
        document.getElementById('gallery-editor-thumbnail').checked = button.dataset.imageThumbnail === '1';
        fileInput.value = '';
        load(button.dataset.imageUrl);
    }));
    const closeEditor = () => { editor.classList.add('hidden'); editor.classList.remove('flex'); document.body.classList.remove('overflow-hidden'); };
    document.querySelector('[data-gallery-close]').addEventListener('click', closeEditor);
    editor.addEventListener('click', (event) => { if (event.target === editor) closeEditor(); });
    fileInput.addEventListener('change', () => { if (fileInput.files[0]) load(URL.createObjectURL(fileInput.files[0])); });
    canvas.addEventListener('pointerdown', (event) => {
        const r = canvas.getBoundingClientRect();
        const point = { x: event.clientX - r.left, y: event.clientY - r.top };
        dragging = true;
        moving = !selecting && point.x >= crop.x && point.x <= crop.x + crop.size && point.y >= crop.y && point.y <= crop.y + crop.size;
        if (moving) {
            moveOffset = { x: point.x - crop.x, y: point.y - crop.y };
        } else {
            start = point;
        }
        canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!dragging) return;
        const r = canvas.getBoundingClientRect();
        const point = { x: event.clientX - r.left, y: event.clientY - r.top };
        if (moving) {
            crop.x = Math.max(0, Math.min(canvas.width - crop.size, point.x - moveOffset.x));
            crop.y = Math.max(0, Math.min(canvas.height - crop.size, point.y - moveOffset.y));
        } else {
            const x = Math.max(0, Math.min(start.x, point.x));
            const y = Math.max(0, Math.min(start.y, point.y));
            const size = Math.min(Math.abs(point.x - start.x), Math.abs(point.y - start.y), canvas.width - x, canvas.height - y);
            crop = { x, y, size };
            if (size > 0) selecting = false;
        }
        draw();
    });
    const stopDragging = () => { dragging = false; moving = false; moveOffset = null; };
    canvas.addEventListener('pointerup', stopDragging);
    canvas.addEventListener('pointercancel', stopDragging);
    document.getElementById('gallery-new-selection').addEventListener('click', () => {
        selecting = true;
        crop = { x: 0, y: 0, size: 0 };
        draw();
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!source.complete || !source.naturalWidth || crop.size <= 0) {
            alert('Please wait for the image preview to finish loading, then try again.');
            return;
        }
        const scale = source.naturalWidth / canvas.width;
        const output = document.createElement('canvas');
        output.width = 1200;
        output.height = 1200;
        output.getContext('2d').drawImage(source, crop.x * scale, crop.y * scale, crop.size * scale, crop.size * scale, 0, 0, 1200, 1200);
        const saveButton = form.querySelector('button[type="submit"]');
        if (saveButton) { saveButton.disabled = true; saveButton.textContent = 'Saving…'; }
        output.toBlob((blob) => {
            if (!blob) {
                if (saveButton) { saveButton.disabled = false; saveButton.textContent = 'Crop & save'; }
                alert('The cropped image could not be created. Please try again.');
                return;
            }
            const croppedFile = new File([blob], 'cropped-product-image.jpg', { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            fileInput.files = transfer.files;
            HTMLFormElement.prototype.submit.call(form);
        }, 'image/jpeg', 0.9);
    });
})();
</script>
@endsection
