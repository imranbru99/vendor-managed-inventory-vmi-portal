<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'acknowledged_qty',
        'received_qty',
        'unit_price',
        'discount',
        'tax',
        'line_total',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'acknowledged_qty' => 'decimal:4',
            'received_qty' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount' => 'decimal:4',
            'tax' => 'decimal:4',
            'line_total' => 'decimal:4',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function shipmentLines(): HasMany
    {
        return $this->hasMany(ShipmentLine::class, 'purchase_order_line_id');
    }

    public function goodsReceiptLines(): HasMany
    {
        return $this->hasMany(GoodsReceiptLine::class, 'purchase_order_line_id');
    }
}
