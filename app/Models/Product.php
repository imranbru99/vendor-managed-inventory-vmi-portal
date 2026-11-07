<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'barcode',
        'gtin',
        'brand',
        'manufacturer',
        'base_uom_id',
        'case_quantity',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dimension_unit',
        'tax_category',
        'status',
        'shelf_life_days',
        'storage_requirements',
        'specifications',
        'image_path',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'length' => 'decimal:4',
            'width' => 'decimal:4',
            'height' => 'decimal:4',
            'specifications' => 'array',
            'meta' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_uom_id');
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function vmiPolicies(): HasMany
    {
        return $this->hasMany(VmiPolicy::class);
    }
}
