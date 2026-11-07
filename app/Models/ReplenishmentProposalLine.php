<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplenishmentProposalLine extends Model
{
    protected $fillable = [
        'proposal_id',
        'recommendation_id',
        'product_id',
        'warehouse_id',
        'policy_id',
        'recommended_qty',
        'proposed_qty',
        'approved_qty',
        'unit_price',
        'line_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recommended_qty' => 'decimal:4',
            'proposed_qty' => 'decimal:4',
            'approved_qty' => 'decimal:4',
            'unit_price' => 'decimal:4',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ReplenishmentProposal::class, 'proposal_id');
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(ReplenishmentRecommendation::class, 'recommendation_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(VmiPolicy::class, 'policy_id');
    }
}
