@extends('layouts.admin')

@section('title', 'Attributes')

@section('content')
<section class="mx-auto max-w-5xl px-6 py-10 lg:px-10">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">Attributes</h1>
            <p class="mt-2 text-slate-500">Manage variant attributes and selectable values.</p>
        </div>
        <a href="{{ route('admin.attributes.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 font-semibold text-white">
            Add attribute
        </a>
    </div>

    @if (session('success'))
        <div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-6 py-3">Attribute</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3">Values</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attributes as $attribute)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $attribute->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $attribute->slug }}</td>
                        <td class="px-6 py-4">{{ $attribute->values_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.attributes.edit', $attribute) }}" class="font-semibold text-indigo-700">
                                    Manage
                                </a>
                                <form method="POST" action="{{ route('admin.attributes.delete', $attribute) }}" onsubmit="return confirm('Delete this attribute and all unused values?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-semibold text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-500">No attributes yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $attributes->links() }}
    </div>
</section>
@endsection
