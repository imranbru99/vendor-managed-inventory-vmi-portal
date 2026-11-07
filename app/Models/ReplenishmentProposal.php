<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReplenishmentProposal extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'relationship_id',
        'proposal_number',
        'status',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'total_qty',
        'total_value',
        'currency_code',
        'requested_delivery_date',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'total_qty' => 'decimal:4',
            'total_value' => 'decimal:4',
            'requested_delivery_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReplenishmentProposalLine::class, 'proposal_id');
    }
}
