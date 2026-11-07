<?php

namespace App\Models;

use App\Enums\ReplenishmentPolicyType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VmiPolicy extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'agreement_id',
        'product_id',
        'warehouse_id',
        'policy_type',
        'min_stock',
        'max_stock',
        'safety_stock',
        'reorder_point',
        'target_days_of_supply',
        'replenishment_frequency_days',
        'lead_time_days',
        'service_level_target',
        'moq',
        'order_multiple',
        'max_order_qty',
        'requires_approval',
        'auto_approve_max_qty',
        'auto_approve_max_value',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'policy_type' => ReplenishmentPolicyType::class,
            'min_stock' => 'decimal:4',
            'max_stock' => 'decimal:4',
            'safety_stock' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'service_level_target' => 'decimal:4',
            'moq' => 'decimal:4',
            'order_multiple' => 'decimal:4',
            'max_order_qty' => 'decimal:4',
            'requires_approval' => 'boolean',
            'auto_approve_max_qty' => 'decimal:4',
            'auto_approve_max_value' => 'decimal:4',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(VmiAgreement::class, 'agreement_id');
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
