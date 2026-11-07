<?php

namespace App\Models;

use App\Enums\RelationshipStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerSupplierRelationship extends Model
{
    protected $fillable = [
        'buyer_organization_id',
        'supplier_organization_id',
        'status',
        'approved_at',
        'suspended_at',
        'notes',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => RelationshipStatus::class,
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function buyerOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'buyer_organization_id');
    }

    public function supplierOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'supplier_organization_id');
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'relationship_id');
    }

    public function vmiAgreements(): HasMany
    {
        return $this->hasMany(VmiAgreement::class, 'relationship_id');
    }

    public function replenishmentProposals(): HasMany
    {
        return $this->hasMany(ReplenishmentProposal::class, 'relationship_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'relationship_id');
    }
}
