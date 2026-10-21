<aside data-category-drawer class="fixed inset-y-0 left-0 z-[70] w-80 max-w-[85vw] -translate-x-full overflow-y-auto rounded-r-2xl border border-slate-100 bg-white px-4 py-5 shadow-2xl transition-transform duration-200 lg:hidden">
    <div class="mb-2 flex items-center justify-between border-b border-slate-100 pb-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
        <span>Shop by category</span>
        <button type="button" data-category-drawer-close class="rounded-lg px-2 py-1 text-lg text-slate-500" aria-label="Close categories">×</button>
    </div>
    <ul class="text-[13px] font-medium text-slate-700">
        @forelse ($categories as $category)
            <li class="border-b border-slate-100/80 py-1 last:border-b-0">
                <a href="{{ route('shop', ['category' => $category->slug]) }}" class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-orange-50">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-sm font-bold text-[var(--brand-primary)]">
                        @if($category->icon_path)
                            <img src="{{ asset('storage/media/'.$category->icon_path) }}" alt="" class="h-full w-full object-cover">
                        @else
                            ✦
                        @endif
                    </span>
                    <span class="min-w-0 break-words text-[12px] leading-4">{{ $category->name }}</span>
                </a>
            </li>
        @empty
            <li class="py-2 text-slate-500">No categories yet.</li>
        @endforelse
    </ul>
    <a href="{{ route('shop') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-slate-50 py-2.5 text-xs font-bold text-[var(--brand-primary)]">View all categories</a>
</aside>
<div data-category-drawer-overlay class="fixed inset-0 z-[60] hidden bg-slate-950/40 lg:hidden"></div>
