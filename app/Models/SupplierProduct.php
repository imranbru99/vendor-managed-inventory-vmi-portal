<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    protected $fillable = [
        'relationship_id',
        'product_id',
        'supplier_sku',
        'unit_price',
        'currency_code',
        'moq',
        'order_multiple',
        'max_order_qty',
        'lead_time_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:4',
            'moq' => 'decimal:4',
            'order_multiple' => 'decimal:4',
            'max_order_qty' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
