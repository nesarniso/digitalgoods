<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Digital product access</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 p-6 text-slate-900">
<main class="mx-auto max-w-xl rounded-2xl bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-black">{{ $license->product->name }}</h1>
    <p class="mt-2 text-sm text-slate-500">Your payment is confirmed. Keep these details private.</p>
    <div class="mt-6 rounded-xl bg-slate-900 p-5 text-white"><p class="text-xs uppercase tracking-wide text-slate-300">License / credential</p><pre class="mt-3 whitespace-pre-wrap break-words font-mono text-lg">{{ $secret }}</pre></div>
    @if($instructions)<div class="mt-6 rounded-xl bg-indigo-50 p-4 text-sm text-indigo-900"><h2 class="font-bold">Instructions</h2><p class="mt-2 whitespace-pre-wrap">{{ $instructions }}</p></div>@endif
    <a href="{{ $downloadUrl }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Download PDF</a>
</main>
</body>
</html>
