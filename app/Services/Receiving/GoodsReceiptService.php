<?php

namespace App\Services\Receiving;

use App\Enums\GoodsReceiptStatus;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use App\Models\Warehouse;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoodsReceiptService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected PurchaseOrderService $purchaseOrderService,
        protected AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<int, array{purchase_order_line_id: int, product_id: int, received_qty: float, damaged_qty?: float, rejected_qty?: float, notes?: string}>  $lines
     */
    public function receive(
        PurchaseOrder $purchaseOrder,
        Warehouse $warehouse,
        array $lines,
        ?Shipment $shipment = null,
        ?string $notes = null,
    ): GoodsReceipt {
        if ($lines === []) {
            throw new InvalidArgumentException('At least one receipt line is required.');
        }

        return DB::transaction(function () use ($purchaseOrder, $warehouse, $lines, $shipment, $notes): GoodsReceipt {
            $goodsReceipt = GoodsReceipt::query()->create([
                'tenant_id' => $purchaseOrder->tenant_id,
                'warehouse_id' => $warehouse->id,
                'purchase_order_id' => $purchaseOrder->id,
                'shipment_id' => $shipment?->id,
                'grn_number' => $this->nextGrnNumber($purchaseOrder->tenant_id),
                'status' => GoodsReceiptStatus::InProgress,
                'received_by' => Auth::id(),
                'received_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                $poLine = $purchaseOrder->lines()->whereKey($line['purchase_order_line_id'])->firstOrFail();
                $receivedQty = (float) $line['received_qty'];
                $damagedQty = (float) ($line['damaged_qty'] ?? 0);
                $rejectedQty = (float) ($line['rejected_qty'] ?? 0);
                $netQty = max(0, $receivedQty - $damagedQty - $rejectedQty);

                GoodsReceiptLine::query()->create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'purchase_order_line_id' => $poLine->id,
                    'product_id' => $line['product_id'],
                    'expected_qty' => $poLine->quantity,
                    'received_qty' => $receivedQty,
                    'damaged_qty' => $damagedQty,
                    'rejected_qty' => $rejectedQty,
                    'notes' => $line['notes'] ?? null,
                ]);

                if ($netQty > 0) {
                    $product = Product::query()->findOrFail($line['product_id']);
                    $this->inventoryService->receive($product, $warehouse, $netQty, [
                        'reference_type' => GoodsReceipt::class,
                        'reference_id' => $goodsReceipt->id,
                        'unit_cost' => $poLine->unit_price,
                        'notes' => 'Goods receipt posting',
                    ]);
                }

                $poLine->received_qty = (float) $poLine->received_qty + $receivedQty;
                $poLine->save();
            }

            $goodsReceipt->update(['status' => GoodsReceiptStatus::Completed]);

            $this->purchaseOrderService->updateFulfillmentFromReceipt($purchaseOrder->fresh('lines'));

            $this->auditLogger->log('goods_receipt.completed', $goodsReceipt, null, $goodsReceipt->toArray());

            return $goodsReceipt->fresh('lines');
        });
    }

    protected function nextGrnNumber(int $tenantId): string
    {
        $sequence = GoodsReceipt::query()->where('tenant_id', $tenantId)->count() + 1;

        return sprintf('GRN-%s-%04d', now()->format('Ymd'), $sequence);
    }
}
