<?php

namespace App\Services\Logistics;

use App\Enums\ShipmentStatus;
use App\Models\AdvanceShippingNotice;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use App\Models\ShipmentLine;
use App\Models\ShipmentTrackingEvent;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShipmentService
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function createShipment(PurchaseOrder $purchaseOrder, array $lines, array $attributes = []): Shipment
    {
        return DB::transaction(function () use ($purchaseOrder, $lines, $attributes): Shipment {
            $shipmentNumber = $attributes['shipment_number'] ?? $this->nextShipmentNumber($purchaseOrder->tenant_id);

            $shipment = Shipment::query()->create([
                'tenant_id' => $purchaseOrder->tenant_id,
                'purchase_order_id' => $purchaseOrder->id,
                'relationship_id' => $purchaseOrder->relationship_id,
                'shipment_number' => $shipmentNumber,
                'carrier' => $attributes['carrier'] ?? null,
                'tracking_number' => $attributes['tracking_number'] ?? null,
                'status' => ShipmentStatus::Draft,
                'notes' => $attributes['notes'] ?? null,
                'meta' => $attributes['meta'] ?? null,
            ]);

            foreach ($lines as $line) {
                ShipmentLine::query()->create([
                    'shipment_id' => $shipment->id,
                    'purchase_order_line_id' => $line['purchase_order_line_id'],
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                ]);
            }

            $this->auditLogger->log('shipment.created', $shipment, null, $shipment->toArray());

            return $shipment->fresh('lines');
        });
    }

    public function dispatch(Shipment $shipment, ?\DateTimeInterface $eta = null): Shipment
    {
        if ($shipment->status !== ShipmentStatus::Draft && $shipment->status !== ShipmentStatus::Pending) {
            throw new InvalidArgumentException('Shipment cannot be dispatched from current status.');
        }

        $old = $shipment->toArray();

        $shipment->update([
            'status' => ShipmentStatus::InTransit,
            'dispatched_at' => now(),
            'eta' => $eta,
        ]);

        $this->addTracking($shipment, ShipmentStatus::InTransit->value, 'Shipment dispatched', now());

        $this->auditLogger->log('shipment.dispatched', $shipment, $old, $shipment->fresh()->toArray());

        return $shipment->fresh();
    }

    public function addTracking(
        Shipment $shipment,
        string $status,
        ?string $description = null,
        ?\DateTimeInterface $occurredAt = null,
        ?string $location = null,
        array $meta = [],
    ): ShipmentTrackingEvent {
        $event = ShipmentTrackingEvent::query()->create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'description' => $description,
            'occurred_at' => $occurredAt ?? now(),
            'location' => $location,
            'meta' => $meta ?: null,
        ]);

        if ($status === ShipmentStatus::Delivered->value) {
            $shipment->update([
                'status' => ShipmentStatus::Delivered,
                'delivered_at' => $occurredAt ?? now(),
            ]);
        }

        return $event;
    }

    public function createAsn(Shipment $shipment, ?\DateTimeInterface $expectedDeliveryAt = null, ?array $payload = null): AdvanceShippingNotice
    {
        $asn = AdvanceShippingNotice::query()->create([
            'tenant_id' => $shipment->tenant_id,
            'shipment_id' => $shipment->id,
            'purchase_order_id' => $shipment->purchase_order_id,
            'asn_number' => $this->nextAsnNumber($shipment->tenant_id),
            'status' => 'sent',
            'expected_delivery_at' => $expectedDeliveryAt ?? $shipment->eta,
            'payload' => $payload ?? [
                'shipment_number' => $shipment->shipment_number,
                'lines' => $shipment->lines()->get(['product_id', 'quantity'])->toArray(),
                'created_by' => Auth::id(),
            ],
        ]);

        $this->auditLogger->log('shipment.asn_created', $asn, null, $asn->toArray());

        return $asn;
    }

    public function markDelivered(Shipment $shipment, ?\DateTimeInterface $deliveredAt = null): Shipment
    {
        $old = $shipment->toArray();

        $deliveredAt ??= now();

        $shipment->update([
            'status' => ShipmentStatus::Delivered,
            'delivered_at' => $deliveredAt,
        ]);

        $this->addTracking($shipment, ShipmentStatus::Delivered->value, 'Shipment delivered', $deliveredAt);

        $this->auditLogger->log('shipment.delivered', $shipment, $old, $shipment->fresh()->toArray());

        return $shipment->fresh();
    }

    protected function nextShipmentNumber(int $tenantId): string
    {
        $sequence = Shipment::query()->where('tenant_id', $tenantId)->count() + 1;

        return sprintf('SHP-%s-%04d', now()->format('Ymd'), $sequence);
    }

    protected function nextAsnNumber(int $tenantId): string
    {
        $sequence = AdvanceShippingNotice::query()->where('tenant_id', $tenantId)->count() + 1;

        return sprintf('ASN-%s-%04d', now()->format('Ymd'), $sequence);
    }
}
