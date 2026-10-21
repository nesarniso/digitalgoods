<div style="margin:0;background:#f8fafc;padding:32px 16px;font-family:Arial,sans-serif;color:#334155">
    <div style="max-width:620px;margin:0 auto;overflow:hidden;border:1px solid #e2e8f0;border-radius:18px;background:#ffffff;box-shadow:0 8px 30px rgba(15,23,42,.08)">
        <div style="padding:24px 28px;background:linear-gradient(135deg,#111827,#3730a3);color:#ffffff">
            <div style="font-size:22px;font-weight:800;letter-spacing:-.02em">{{ config('app.name') }}</div>
            <div style="margin-top:5px;font-size:12px;opacity:.75">Customer communication</div>
        </div>
        <div style="padding:30px 28px;line-height:1.75;font-size:15px">
            {!! $body !!}
        </div>
        <div style="padding:18px 28px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px">
            This is an automated email from {{ config('app.name') }}. Please do not reply directly.
        </div>
    </div>
</div>
