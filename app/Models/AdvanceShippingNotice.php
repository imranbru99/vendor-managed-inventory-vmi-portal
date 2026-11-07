<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceShippingNotice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'shipment_id',
        'purchase_order_id',
        'asn_number',
        'status',
        'expected_delivery_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'expected_delivery_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
