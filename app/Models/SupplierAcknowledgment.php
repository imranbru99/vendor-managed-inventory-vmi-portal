<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierAcknowledgment extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'acknowledged_by',
        'status',
        'notes',
        'confirmed_delivery_date',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_delivery_date' => 'date',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
