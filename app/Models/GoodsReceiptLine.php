<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptLine extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_line_id',
        'product_id',
        'expected_qty',
        'received_qty',
        'damaged_qty',
        'rejected_qty',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_qty' => 'decimal:4',
            'received_qty' => 'decimal:4',
            'damaged_qty' => 'decimal:4',
            'rejected_qty' => 'decimal:4',
        ];
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function purchaseOrderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
