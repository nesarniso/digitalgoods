<?php

namespace App\Domain\Orders\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Orders\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\CommerceNotification;
use App\Domain\Finance\Services\ProfitService;

class ReturnRequestService
{
    public function __construct(private readonly AuditLogger $audit, private readonly ProfitService $profits) {}

    public function create(User $user, Order $order, OrderItem $item, int $quantity, string $reason, ?string $note): ReturnRequest
    {
        if ((int) $order->user_id !== (int) $user->id || (int) $item->order_id !== (int) $order->id) {
            abort(404);
        }

        return DB::transaction(function () use ($user, $order, $item, $quantity, $reason, $note) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedItem = OrderItem::query()
                ->whereKey($item->id)
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedOrder->status, ['delivered', 'returned'], true)) {
                throw ValidationException::withMessages(['order' => ['Returns can only be requested after delivery.']]);
            }

            if ($lockedItem->order_id !== $lockedOrder->id) {
                abort(404);
            }

            $alreadyRequested = (int) $lockedItem->returnRequests()
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->sum('quantity');

            if ($quantity < 1 || $quantity + $alreadyRequested > $lockedItem->quantity) {
                throw ValidationException::withMessages(['quantity' => ['The requested quantity exceeds the remaining returnable quantity.']]);
            }

            $request = ReturnRequest::create([
                'uuid' => (string) Str::uuid(),
                'request_number' => 'RET-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'order_id' => $lockedOrder->id,
                'order_item_id' => $lockedItem->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'reason' => $reason,
                'customer_note' => $note,
                'status' => 'requested',
                'requested_at' => now(),
            ]);
            $this->audit->log($user, 'requested', 'Returns', 'ReturnRequest', (string) $request->id, newValue: ['status' => 'requested']);
            return $request;
        });
    }

    public function updateStatus(ReturnRequest $request, string $status, ?string $note, User $actor): ReturnRequest
    {
        if (! in_array($status, ['approved', 'rejected', 'received', 'refunded', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => ['Invalid return status.']]);
        }
        return DB::transaction(function () use ($request, $status, $note, $actor) {
            $request = ReturnRequest::query()
                ->whereKey($request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($request->status, ['refunded', 'cancelled'], true)) {
                throw ValidationException::withMessages(['status' => ['This return request is already closed.']]);
            }

            $request->update([
                'status' => $status,
                'admin_note' => $note,
                'resolved_at' => in_array($status, ['rejected', 'refunded', 'cancelled'], true) ? now() : null,
            ]);

            if ($status === 'refunded') {
                $this->profits->reverseForReturn($request);
            }

            $request->loadMissing(['user', 'order']);
            $request->user?->notify(new CommerceNotification(
                'Return request updated',
                "Return {$request->request_number} is now ".str_replace('_', ' ', $status).'.',
                route('account.orders.show', $request->order),
                'return_request_updated',
                [
                    'customer_name' => $request->user?->name ?? 'Customer',
                    'request_number' => $request->request_number,
                    'return_status' => str_replace('_', ' ', $status),
                    'order_url' => route('account.orders.show', $request->order),
                ],
            ));
            $this->audit->log($actor, 'status_updated', 'Returns', 'ReturnRequest', (string) $request->id, newValue: ['status' => $status]);

            return $request->fresh();
        });
    }
}
