<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'relationship_id',
        'shipment_number',
        'carrier',
        'tracking_number',
        'status',
        'dispatched_at',
        'eta',
        'delivered_at',
        'shipping_cost',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'dispatched_at' => 'datetime',
            'eta' => 'datetime',
            'delivered_at' => 'datetime',
            'shipping_cost' => 'decimal:4',
            'meta' => 'array',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ShipmentLine::class);
    }
}
