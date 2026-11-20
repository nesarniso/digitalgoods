<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $order->order_number }} | {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; font-family: Arial, sans-serif; }
        body { margin: 0; background: #f1f5f9; color: #0f172a; }
        .receipt { box-sizing: border-box; width: min(100% - 32px, 800px); margin: 32px auto; padding: 36px; background: #fff; }
        .toolbar { display: flex; justify-content: space-between; gap: 12px; margin: 0 auto 16px; width: min(100% - 32px, 800px); }
        button, a { border: 0; border-radius: 8px; padding: 10px 16px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .print { background: #4f46e5; color: #fff; }
        .back { background: #fff; color: #334155; }
        h1, h2, p { margin: 0; }
        h1 { font-size: 26px; }
        h2 { margin-bottom: 12px; font-size: 16px; }
        .muted { color: #64748b; }
        .header, .summary, .customer { display: flex; justify-content: space-between; gap: 24px; }
        .header { padding-bottom: 24px; border-bottom: 2px solid #e2e8f0; }
        .customer { padding: 24px 0; border-bottom: 1px solid #e2e8f0; }
        .customer > div { flex: 1; }
        .customer p { line-height: 1.6; }
        table { width: 100%; margin-top: 24px; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 12px 0; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .summary { justify-content: flex-end; margin-top: 20px; }
        .summary dl { width: 260px; margin: 0; }
        .summary div { display: flex; justify-content: space-between; padding: 6px 0; }
        .total { border-top: 2px solid #0f172a; font-size: 18px; font-weight: 700; }
        .paid { color: #059669; font-weight: 700; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .receipt { width: 100%; margin: 0; padding: 0; }
        }
        @media (max-width: 600px) {
            .receipt { padding: 20px; }
            .header, .customer { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a class="back" href="{{ route('admin.orders.show', $order) }}">Back to order</a>
        <button class="print" type="button" onclick="window.print()">Print / Download PDF</button>
    </div>
    @php($address = $order->addresses->firstWhere('type', 'shipping') ?? $order->addresses->first())
    @php($paymentStatus = $paymentTransaction?->isSuccessful() ? 'Paid' : (in_array($paymentTransaction?->status, ['pending', 'initiated'], true) ? 'Pending' : ($paymentTransaction ? Str::headline($paymentTransaction->status) : 'Pending')))
    <main class="receipt">
        <header class="header">
            <div>
                <h1>{{ config('app.name') }}</h1>
                <p class="muted">Order receipt</p>
            </div>
            <div>
                <p><strong>{{ $order->order_number }}</strong></p>
                <p class="muted">{{ $order->placed_at?->format('d M Y, h:i A') }}</p>
            </div>
        </header>
        <section class="customer">
            <div>
                <h2>Customer</h2>
                <p>{{ $address?->name ?: $order->user?->name ?: 'Guest customer' }}</p>
                <p>{{ $order->customer_email ?: $order->user?->email ?: '—' }}</p>
                <p>{{ $address?->phone ?: $order->customer_phone ?: '—' }}</p>
            </div>
            <div>
                <h2>Delivery address</h2>
                <p>{{ collect([$address?->address_line, $address?->area, $address?->upazila, $address?->district, $address?->division])->filter()->implode(', ') ?: 'No delivery address available' }}</p>
            </div>
        </section>
        <table>
            <thead><tr><th>Item</th><th>Qty</th><th>Unit price</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr><td>{{ $item->product_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }}</td><td>{{ $item->quantity }}</td><td>₹{{ number_format((float) $item->unit_price, 2) }}</td><td>₹{{ number_format((float) $item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="summary">
            <dl>
                <div><dt>Subtotal</dt><dd>₹{{ number_format((float) $order->subtotal, 2) }}</dd></div>
                <div><dt>Discount</dt><dd>- ₹{{ number_format((float) $order->discount_amount, 2) }}</dd></div>
                <div><dt>Shipping</dt><dd>₹{{ number_format((float) $order->shipping_amount, 2) }}</dd></div>
                <div class="total"><dt>Total</dt><dd>₹{{ number_format((float) $order->grand_total, 2) }}</dd></div>
            </dl>
        </div>
        <p style="margin-top: 24px">Payment: <strong>{{ Str::headline($order->payment_method) }}</strong> · <span class="paid">{{ $paymentStatus }}</span></p>
    </main>
</body>
</html>
