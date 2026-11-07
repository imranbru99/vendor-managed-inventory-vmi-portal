<?php

namespace App\Services\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\ReplenishmentProposal;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrderService
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function generateFromProposal(ReplenishmentProposal $proposal, ?string $idempotencyKey = null): PurchaseOrder
    {
        if ($idempotencyKey) {
            $existing = PurchaseOrder::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($proposal, $idempotencyKey): PurchaseOrder {
            $lines = $proposal->lines()->where('line_status', '!=', 'rejected')->get();

            if ($lines->isEmpty()) {
                throw new InvalidArgumentException('Proposal has no lines to convert.');
            }

            $poNumber = $this->nextPoNumber($proposal->tenant_id);
            $subtotal = 0.0;

            $purchaseOrder = PurchaseOrder::query()->create([
                'tenant_id' => $proposal->tenant_id,
                'relationship_id' => $proposal->relationship_id,
                'proposal_id' => $proposal->id,
                'po_number' => $poNumber,
                'status' => PurchaseOrderStatus::Draft,
                'currency_code' => $proposal->currency_code,
                'required_delivery_date' => $proposal->requested_delivery_date,
                'delivery_warehouse_id' => $lines->first()->warehouse_id,
                'created_by' => Auth::id(),
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($lines as $line) {
                $qty = (float) ($line->approved_qty ?? $line->proposed_qty);
                $unitPrice = (float) ($line->unit_price ?? 0);
                $lineTotal = round($qty * $unitPrice, 4);
                $subtotal += $lineTotal;

                PurchaseOrderLine::query()->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $line->product_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'status' => 'open',
                ]);
            }

            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'grand_total' => $subtotal,
            ]);

            $this->auditLogger->log('purchase_order.generated', $purchaseOrder, null, $purchaseOrder->toArray());

            return $purchaseOrder->fresh('lines');
        });
    }

    public function issue(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft) {
            throw new InvalidArgumentException('Only draft purchase orders can be issued.');
        }

        $old = $purchaseOrder->toArray();
        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Submitted,
            'issued_at' => now(),
        ]);

        $this->auditLogger->log('purchase_order.issued', $purchaseOrder, $old, $purchaseOrder->fresh()->toArray());

        return $purchaseOrder->fresh();
    }

    public function acknowledge(PurchaseOrder $purchaseOrder, ?float $acknowledgedQty = null): PurchaseOrder
    {
        $old = $purchaseOrder->toArray();

        if ($acknowledgedQty !== null) {
            $purchaseOrder->lines()->first()?->update(['acknowledged_qty' => $acknowledgedQty]);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Confirmed,
            'acknowledged_at' => now(),
        ]);

        $this->auditLogger->log('purchase_order.acknowledged', $purchaseOrder, $old, $purchaseOrder->fresh()->toArray());

        return $purchaseOrder->fresh('lines');
    }

    public function cancel(PurchaseOrder $purchaseOrder, ?string $notes = null): PurchaseOrder
    {
        $old = $purchaseOrder->toArray();

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Cancelled,
            'notes' => $notes ?? $purchaseOrder->notes,
            'closed_at' => now(),
        ]);

        $purchaseOrder->lines()->update(['status' => 'cancelled']);

        $this->auditLogger->log('purchase_order.cancelled', $purchaseOrder, $old, $purchaseOrder->fresh()->toArray());

        return $purchaseOrder->fresh();
    }

    public function updateFulfillmentFromReceipt(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder->load('lines');

        $allReceived = true;
        $anyReceived = false;

        foreach ($purchaseOrder->lines as $line) {
            if ((float) $line->received_qty > 0) {
                $anyReceived = true;
            }
            if ((float) $line->received_qty < (float) $line->quantity) {
                $allReceived = false;
            }

            $line->status = (float) $line->received_qty >= (float) $line->quantity
                ? 'fulfilled'
                : ((float) $line->received_qty > 0 ? 'partial' : 'open');
            $line->save();
        }

        $status = $allReceived
            ? PurchaseOrderStatus::Received
            : ($anyReceived ? PurchaseOrderStatus::PartiallyReceived : $purchaseOrder->status);

        $purchaseOrder->update([
            'status' => $status,
            'closed_at' => $allReceived ? now() : $purchaseOrder->closed_at,
        ]);

        return $purchaseOrder->fresh('lines');
    }

    protected function nextPoNumber(int $tenantId): string
    {
        $sequence = PurchaseOrder::query()->where('tenant_id', $tenantId)->count() + 1;

        return sprintf('PO-%s-%04d', now()->format('Ymd'), $sequence);
    }
}
