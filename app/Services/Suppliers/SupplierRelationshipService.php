<?php

namespace App\Services\Suppliers;

use App\Enums\RelationshipStatus;
use App\Models\BuyerSupplierRelationship;
use App\Models\Organization;
use App\Services\Audit\AuditLogger;
use InvalidArgumentException;

class SupplierRelationshipService
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function invite(Organization $buyer, Organization $supplier, ?string $notes = null): BuyerSupplierRelationship
    {
        if ($buyer->tenant_id !== $supplier->tenant_id) {
            throw new InvalidArgumentException('Buyer and supplier must belong to the same tenant.');
        }

        $relationship = BuyerSupplierRelationship::query()->firstOrCreate(
            [
                'buyer_organization_id' => $buyer->id,
                'supplier_organization_id' => $supplier->id,
            ],
            [
                'status' => RelationshipStatus::Pending,
                'notes' => $notes,
            ],
        );

        $this->auditLogger->log('relationship.invited', $relationship, null, $relationship->toArray());

        return $relationship;
    }

    public function approve(BuyerSupplierRelationship $relationship, ?string $notes = null): BuyerSupplierRelationship
    {
        $old = $relationship->toArray();

        $relationship->update([
            'status' => RelationshipStatus::Approved,
            'approved_at' => now(),
            'notes' => $notes ?? $relationship->notes,
        ]);

        $this->auditLogger->log('relationship.approved', $relationship, $old, $relationship->fresh()->toArray());

        return $relationship->fresh();
    }

    public function reject(BuyerSupplierRelationship $relationship, ?string $notes = null): BuyerSupplierRelationship
    {
        $old = $relationship->toArray();

        $relationship->update([
            'status' => RelationshipStatus::Terminated,
            'notes' => $notes ?? $relationship->notes,
        ]);

        $this->auditLogger->log('relationship.rejected', $relationship, $old, $relationship->fresh()->toArray());

        return $relationship->fresh();
    }

    public function suspend(BuyerSupplierRelationship $relationship, ?string $notes = null): BuyerSupplierRelationship
    {
        $old = $relationship->toArray();

        $relationship->update([
            'status' => RelationshipStatus::Suspended,
            'suspended_at' => now(),
            'notes' => $notes ?? $relationship->notes,
        ]);

        $this->auditLogger->log('relationship.suspended', $relationship, $old, $relationship->fresh()->toArray());

        return $relationship->fresh();
    }
}
