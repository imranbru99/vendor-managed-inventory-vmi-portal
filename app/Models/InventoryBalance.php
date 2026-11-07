<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'location_id',
        'product_id',
        'on_hand',
        'sellable',
        'reserved',
        'allocated',
        'quarantined',
        'damaged',
        'in_transit',
        'confirmed_inbound',
        'consignment',
        'returned',
        'average_cost',
        'last_counted_at',
    ];

    protected function casts(): array
    {
        return [
            'on_hand' => 'decimal:4',
            'sellable' => 'decimal:4',
            'reserved' => 'decimal:4',
            'allocated' => 'decimal:4',
            'quarantined' => 'decimal:4',
            'damaged' => 'decimal:4',
            'in_transit' => 'decimal:4',
            'confirmed_inbound' => 'decimal:4',
            'consignment' => 'decimal:4',
            'returned' => 'decimal:4',
            'average_cost' => 'decimal:4',
            'last_counted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
