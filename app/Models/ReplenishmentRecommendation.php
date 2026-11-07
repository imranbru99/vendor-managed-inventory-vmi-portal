<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplenishmentRecommendation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'run_id',
        'policy_id',
        'product_id',
        'warehouse_id',
        'relationship_id',
        'inventory_position',
        'average_daily_consumption',
        'safety_stock',
        'reorder_point',
        'target_stock',
        'recommended_qty',
        'expected_delivery_date',
        'days_of_inventory',
        'explanation',
        'data_as_of',
        'is_stale',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'inventory_position' => 'decimal:4',
            'average_daily_consumption' => 'decimal:4',
            'safety_stock' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'target_stock' => 'decimal:4',
            'recommended_qty' => 'decimal:4',
            'days_of_inventory' => 'decimal:4',
            'expected_delivery_date' => 'date',
            'data_as_of' => 'datetime',
            'is_stale' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
