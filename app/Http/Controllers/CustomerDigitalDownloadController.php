<?php

namespace App\Http\Controllers;

use App\Domain\Digital\Models\ProductDownloadFile;
use App\Domain\Orders\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerDigitalDownloadController extends Controller
{
    public function download(Request $request, Order $order, ProductDownloadFile $file)
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->isPaid(), 403);
        abort_unless($order->status === 'delivered', 403);
        abort_unless(
            $order->items()
                ->where('product_id', $file->product_id)
                ->whereHas('product', fn ($query) => $query->where('product_type', 'digital'))
                ->exists(),
            404,
        );

        $disk = Storage::disk('local');
        abort_unless($disk->exists($file->path), 404);

        return $disk->download($file->path, $file->original_name, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
