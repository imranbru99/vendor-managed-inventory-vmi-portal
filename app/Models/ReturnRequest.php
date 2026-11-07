<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'relationship_id',
        'purchase_order_id',
        'status',
        'reason',
        'requested_by',
        'approved_by',
        'notes',
    ];

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReturnLine::class, 'return_request_id');
    }
}
