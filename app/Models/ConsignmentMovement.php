<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentMovement extends Model
{
    protected $fillable = [
        'agreement_id',
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'occurred_at' => 'datetime',
        ];
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ConsignmentAgreement::class, 'agreement_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
