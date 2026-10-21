<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\RestockRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RestockRequestService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(Inventory $inventoryRow, int $quantity, ?string $notes, User $actor): RestockRequest
    {
        $request = RestockRequest::create([
            'uuid' => (string) Str::uuid(),
            'inventory_id' => $inventoryRow->id,
            'requested_quantity' => $quantity,
            'status' => 'pending',
            'requested_by' => $actor->id,
            'notes' => $notes,
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Inventory',
            entityType: 'RestockRequest', entityId: (string) $request->id,
            newValue: ['inventory_id' => $inventoryRow->id, 'quantity' => $quantity],
        );

        return $request;
    }

    /**
     * Approving a restock request is the point where physical_stock
     * actually increases — this is the "Approved → Published" style
     * workflow from spec section 40, applied to inventory instead of
     * vendor products.
     */
    public function approve(RestockRequest $request, User $actor): RestockRequest
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only a pending restock request can be approved.'],
            ]);
        }

        return DB::transaction(function () use ($request, $actor) {
            $this->inventory->adjust(
                $request->inventory,
                $request->requested_quantity,
                reason: "Restock request #{$request->id} approved",
                actor: $actor,
            );

            $request->update([
                'status' => 'completed',
                'approved_by' => $actor->id,
            ]);

            $this->auditLogger->log(
                actor: $actor, action: 'approved', module: 'Inventory',
                entityType: 'RestockRequest', entityId: (string) $request->id,
                newValue: ['quantity' => $request->requested_quantity],
            );

            return $request;
        });
    }

    public function reject(RestockRequest $request, string $reason, User $actor): RestockRequest
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only a pending restock request can be rejected.'],
            ]);
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => $actor->id,
            'notes' => trim(($request->notes ?? '')."\nRejected: {$reason}"),
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'rejected', module: 'Inventory',
            entityType: 'RestockRequest', entityId: (string) $request->id,
            newValue: ['reason' => $reason],
        );

        return $request;
    }
}
