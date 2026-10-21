<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Digital license {{ $license->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 14px; }
        .label { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .secret { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 16px; margin-top: 18px; white-space: pre-wrap; word-wrap: break-word; font-family: DejaVu Sans Mono, monospace; }
        .instructions { background: #eef2ff; padding: 14px; margin-top: 18px; white-space: pre-wrap; }
        .footer { color: #64748b; font-size: 10px; margin-top: 28px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $license->product->name }}</h1>
        <p class="label">Digital license / credential</p>
        <p>Reference: {{ $license->reference }}</p>
    </div>
    <div class="secret">{{ $secret }}</div>
    @if($instructions)
        <div class="instructions"><strong>Instructions</strong><br>{{ $instructions }}</div>
    @endif
    <p class="footer">Keep this document and credential private. Payment-confirmed digital delivery.</p>
</body>
</html>
