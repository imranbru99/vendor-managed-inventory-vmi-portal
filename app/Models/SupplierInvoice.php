<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierInvoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'relationship_id',
        'purchase_order_id',
        'invoice_number',
        'status',
        'currency_code',
        'subtotal',
        'tax_total',
        'grand_total',
        'issued_at',
        'due_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'grand_total' => 'decimal:4',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(BuyerSupplierRelationship::class, 'relationship_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplierInvoiceLine::class, 'supplier_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentRecord::class, 'supplier_invoice_id');
    }
}
