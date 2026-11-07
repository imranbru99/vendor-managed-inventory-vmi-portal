<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'slug',
        'legal_name',
        'email',
        'phone',
        'logo_path',
        'tax_id',
        'registration_number',
        'country_code',
        'currency_code',
        'timezone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'status' => OrganizationStatus::class,
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    public function buyerRelationships(): HasMany
    {
        return $this->hasMany(BuyerSupplierRelationship::class, 'buyer_organization_id');
    }

    public function supplierRelationships(): HasMany
    {
        return $this->hasMany(BuyerSupplierRelationship::class, 'supplier_organization_id');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
