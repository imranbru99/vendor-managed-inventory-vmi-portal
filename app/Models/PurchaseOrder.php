<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'relationship_id',
        'proposal_id',
        'po_number',
        'status',
        'currency_code',
        'subtotal',
        'tax_total',
        'discount_total',
        'grand_total',
        'delivery_warehouse_id',
        'required_delivery_date',
        'payment_terms',
        'terms',
        'issued_at',
        'acknowledged_at',
        'closed_at',
        'created_by',
        'notes',
        'idempotency_key',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'subtotal' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'discount_total' => 'decimal:4',
            'grand_total' => 'decimal:4',
            'required_delivery_date' => 'date',
            'issued_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'closed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }
}
