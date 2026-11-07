<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspection extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'goods_receipt_id',
        'product_id',
        'status',
        'result',
        'inspector_id',
        'inspected_at',
        'notes',
        'checklist',
    ];

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
            'checklist' => 'array',
        ];
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
