<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentAgreement extends Model
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
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function balances(): HasMany
    {
        return $this->hasMany(ConsignmentBalance::class, 'agreement_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(ConsignmentMovement::class, 'agreement_id');
    }
}
