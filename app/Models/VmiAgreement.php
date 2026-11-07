<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VmiAgreement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'relationship_id',
        'name',
        'status',
        'starts_at',
        'ends_at',
        'terms',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function policies(): HasMany
    {
        return $this->hasMany(VmiPolicy::class, 'agreement_id');
    }
}
