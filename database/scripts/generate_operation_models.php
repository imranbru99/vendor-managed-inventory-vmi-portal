<?php

$base = dirname(__DIR__, 2).'/app/Models';

$definitions = [
    ['DemandEvent', true, ['product_id', 'warehouse_id', 'event_date', 'quantity', 'source', 'meta'], [
        'product' => 'Product',
        'warehouse' => 'Warehouse',
    ], ['meta' => 'array', 'quantity' => 'decimal:4', 'event_date' => 'date']],
    ['ReplenishmentRun', true, ['relationship_id', 'status', 'started_at', 'completed_at', 'triggered_by', 'summary'], [
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'recommendations' => ['ReplenishmentRecommendation', 'hasMany', 'run_id'],
    ], ['summary' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime']],
    ['ReplenishmentRecommendation', true, [
        'run_id', 'policy_id', 'product_id', 'warehouse_id', 'relationship_id',
        'inventory_position', 'average_daily_consumption', 'safety_stock', 'reorder_point', 'target_stock',
        'recommended_qty', 'expected_delivery_date', 'days_of_inventory', 'explanation', 'data_as_of', 'is_stale', 'meta',
    ], [
        'run' => ['ReplenishmentRun', 'run_id'],
        'policy' => ['VmiPolicy', 'policy_id'],
        'product' => 'Product',
        'warehouse' => 'Warehouse',
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'proposalLines' => ['ReplenishmentProposalLine', 'hasMany', 'recommendation_id'],
    ], [
        'inventory_position' => 'decimal:4', 'average_daily_consumption' => 'decimal:4', 'safety_stock' => 'decimal:4',
        'reorder_point' => 'decimal:4', 'target_stock' => 'decimal:4', 'recommended_qty' => 'decimal:4',
        'days_of_inventory' => 'decimal:4', 'expected_delivery_date' => 'date', 'data_as_of' => 'datetime',
        'is_stale' => 'boolean', 'meta' => 'array',
    ]],
    ['ReplenishmentProposal', true, [
        'relationship_id', 'proposal_number', 'status', 'submitted_by', 'submitted_at', 'reviewed_by', 'reviewed_at',
        'review_notes', 'total_qty', 'total_value', 'currency_code', 'requested_delivery_date', 'meta',
    ], [
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'lines' => ['ReplenishmentProposalLine', 'hasMany', 'proposal_id'],
        'purchaseOrders' => ['PurchaseOrder', 'hasMany', 'proposal_id'],
        'submitter' => ['User', 'submitted_by'],
        'reviewer' => ['User', 'reviewed_by'],
    ], [
        'status' => 'App\\Enums\\ProposalStatus',
        'total_qty' => 'decimal:4', 'total_value' => 'decimal:4',
        'submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'requested_delivery_date' => 'date', 'meta' => 'array',
    ]],
    ['ReplenishmentProposalLine', false, [
        'proposal_id', 'recommendation_id', 'product_id', 'warehouse_id', 'policy_id',
        'recommended_qty', 'proposed_qty', 'approved_qty', 'unit_price', 'line_status', 'notes',
    ], [
        'proposal' => ['ReplenishmentProposal', 'proposal_id'],
        'recommendation' => ['ReplenishmentRecommendation', 'recommendation_id'],
        'product' => 'Product',
        'warehouse' => 'Warehouse',
        'policy' => ['VmiPolicy', 'policy_id'],
    ], [
        'recommended_qty' => 'decimal:4', 'proposed_qty' => 'decimal:4', 'approved_qty' => 'decimal:4', 'unit_price' => 'decimal:4',
    ]],
    ['PurchaseOrder', true, [
        'relationship_id', 'proposal_id', 'po_number', 'status', 'currency_code', 'subtotal', 'tax_total', 'discount_total',
        'grand_total', 'delivery_warehouse_id', 'required_delivery_date', 'payment_terms', 'terms', 'issued_at',
        'acknowledged_at', 'closed_at', 'created_by', 'notes', 'idempotency_key', 'meta',
    ], [
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'proposal' => ['ReplenishmentProposal', 'proposal_id'],
        'deliveryWarehouse' => ['Warehouse', 'delivery_warehouse_id'],
        'creator' => ['User', 'created_by'],
        'lines' => ['PurchaseOrderLine', 'hasMany', 'purchase_order_id'],
        'approvals' => ['PurchaseOrderApproval', 'hasMany', 'purchase_order_id'],
        'acknowledgments' => ['SupplierAcknowledgment', 'hasMany', 'purchase_order_id'],
        'shipments' => ['Shipment', 'hasMany', 'purchase_order_id'],
        'goodsReceipts' => ['GoodsReceipt', 'hasMany', 'purchase_order_id'],
    ], [
        'status' => 'App\\Enums\\PurchaseOrderStatus',
        'subtotal' => 'decimal:4', 'tax_total' => 'decimal:4', 'discount_total' => 'decimal:4', 'grand_total' => 'decimal:4',
        'required_delivery_date' => 'date', 'issued_at' => 'datetime', 'acknowledged_at' => 'datetime', 'closed_at' => 'datetime', 'meta' => 'array',
    ]],
    ['PurchaseOrderLine', false, [
        'purchase_order_id', 'product_id', 'quantity', 'acknowledged_qty', 'received_qty', 'unit_price', 'discount', 'tax', 'line_total', 'status', 'notes',
    ], [
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'product' => 'Product',
        'shipmentLines' => ['ShipmentLine', 'hasMany', 'purchase_order_line_id'],
        'goodsReceiptLines' => ['GoodsReceiptLine', 'hasMany', 'purchase_order_line_id'],
    ], [
        'quantity' => 'decimal:4', 'acknowledged_qty' => 'decimal:4', 'received_qty' => 'decimal:4',
        'unit_price' => 'decimal:4', 'discount' => 'decimal:4', 'tax' => 'decimal:4', 'line_total' => 'decimal:4',
    ]],
    ['PurchaseOrderApproval', false, [
        'purchase_order_id', 'approver_id', 'stage', 'status', 'notes', 'acted_at',
    ], [
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'approver' => ['User', 'approver_id'],
    ], ['acted_at' => 'datetime']],
    ['SupplierAcknowledgment', false, [
        'purchase_order_id', 'acknowledged_by', 'status', 'notes', 'confirmed_delivery_date',
    ], [
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'acknowledger' => ['User', 'acknowledged_by'],
    ], ['confirmed_delivery_date' => 'date']],
    ['Shipment', true, [
        'purchase_order_id', 'relationship_id', 'shipment_number', 'carrier', 'tracking_number', 'status',
        'dispatched_at', 'eta', 'delivered_at', 'shipping_cost', 'notes', 'meta',
    ], [
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'lines' => ['ShipmentLine', 'hasMany', 'shipment_id'],
        'trackingEvents' => ['ShipmentTrackingEvent', 'hasMany', 'shipment_id'],
        'advanceShippingNotices' => ['AdvanceShippingNotice', 'hasMany', 'shipment_id'],
        'goodsReceipts' => ['GoodsReceipt', 'hasMany', 'shipment_id'],
    ], [
        'status' => 'App\\Enums\\ShipmentStatus',
        'dispatched_at' => 'datetime', 'eta' => 'datetime', 'delivered_at' => 'datetime',
        'shipping_cost' => 'decimal:4', 'meta' => 'array',
    ]],
    ['ShipmentLine', false, [
        'shipment_id', 'purchase_order_line_id', 'product_id', 'quantity',
    ], [
        'shipment' => ['Shipment', 'shipment_id'],
        'purchaseOrderLine' => ['PurchaseOrderLine', 'purchase_order_line_id'],
        'product' => 'Product',
    ], ['quantity' => 'decimal:4']],
    ['ShipmentTrackingEvent', false, [
        'shipment_id', 'status', 'description', 'occurred_at', 'location', 'meta',
    ], [
        'shipment' => ['Shipment', 'shipment_id'],
    ], ['occurred_at' => 'datetime', 'meta' => 'array']],
    ['AdvanceShippingNotice', true, [
        'shipment_id', 'purchase_order_id', 'asn_number', 'status', 'expected_delivery_at', 'payload',
    ], [
        'shipment' => ['Shipment', 'shipment_id'],
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
    ], ['expected_delivery_at' => 'datetime', 'payload' => 'array']],
    ['GoodsReceipt', true, [
        'warehouse_id', 'purchase_order_id', 'shipment_id', 'grn_number', 'status', 'received_by', 'received_at', 'notes',
    ], [
        'warehouse' => 'Warehouse',
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'shipment' => ['Shipment', 'shipment_id'],
        'receiver' => ['User', 'received_by'],
        'lines' => ['GoodsReceiptLine', 'hasMany', 'goods_receipt_id'],
        'qualityInspections' => ['QualityInspection', 'hasMany', 'goods_receipt_id'],
    ], [
        'status' => 'App\\Enums\\GoodsReceiptStatus',
        'received_at' => 'datetime',
    ]],
    ['GoodsReceiptLine', false, [
        'goods_receipt_id', 'purchase_order_line_id', 'product_id', 'expected_qty', 'received_qty', 'damaged_qty', 'rejected_qty', 'notes',
    ], [
        'goodsReceipt' => ['GoodsReceipt', 'goods_receipt_id'],
        'purchaseOrderLine' => ['PurchaseOrderLine', 'purchase_order_line_id'],
        'product' => 'Product',
    ], [
        'expected_qty' => 'decimal:4', 'received_qty' => 'decimal:4', 'damaged_qty' => 'decimal:4', 'rejected_qty' => 'decimal:4',
    ]],
    ['WorkflowDefinition', true, [
        'name', 'slug', 'entity_type', 'is_active', 'settings',
    ], [
        'steps' => ['WorkflowStep', 'hasMany', 'workflow_definition_id'],
        'instances' => ['WorkflowInstance', 'hasMany', 'workflow_definition_id'],
    ], ['is_active' => 'boolean', 'settings' => 'array']],
    ['WorkflowStep', false, [
        'workflow_definition_id', 'name', 'stage_order', 'approver_role', 'amount_threshold', 'quantity_threshold', 'is_auto_approve', 'settings',
    ], [
        'definition' => ['WorkflowDefinition', 'workflow_definition_id'],
        'approvals' => ['WorkflowApproval', 'hasMany', 'workflow_step_id'],
    ], [
        'amount_threshold' => 'decimal:4', 'quantity_threshold' => 'decimal:4', 'is_auto_approve' => 'boolean', 'settings' => 'array',
    ]],
    ['WorkflowInstance', true, [
        'workflow_definition_id', 'entity_type', 'entity_id', 'status', 'current_step_id', 'started_at', 'completed_at',
    ], [
        'definition' => ['WorkflowDefinition', 'workflow_definition_id'],
        'currentStep' => ['WorkflowStep', 'current_step_id'],
        'approvals' => ['WorkflowApproval', 'hasMany', 'workflow_instance_id'],
    ], [
        'status' => 'App\\Enums\\WorkflowStatus',
        'started_at' => 'datetime', 'completed_at' => 'datetime',
    ]],
    ['WorkflowApproval', false, [
        'workflow_instance_id', 'workflow_step_id', 'approver_id', 'status', 'notes', 'acted_at',
    ], [
        'instance' => ['WorkflowInstance', 'workflow_instance_id'],
        'step' => ['WorkflowStep', 'workflow_step_id'],
        'approver' => ['User', 'approver_id'],
    ], ['acted_at' => 'datetime']],
    ['CmsPage', false, [
        'title', 'slug', 'locale', 'excerpt', 'body', 'template', 'seo_title', 'seo_description', 'is_published', 'published_at', 'meta',
    ], [], ['is_published' => 'boolean', 'published_at' => 'datetime', 'meta' => 'array']],
    ['BlogPost', false, [
        'title', 'slug', 'locale', 'excerpt', 'body', 'author_id', 'cover_image', 'seo_title', 'seo_description', 'is_published', 'published_at',
    ], [
        'author' => ['User', 'author_id'],
    ], ['is_published' => 'boolean', 'published_at' => 'datetime']],
    ['Faq', false, ['question', 'answer', 'category', 'sort_order', 'is_published'], [], ['is_published' => 'boolean']],
    ['ContactMessage', false, ['name', 'email', 'company', 'phone', 'subject', 'message', 'status'], [], []],
    ['DemoRequest', false, ['name', 'email', 'company', 'phone', 'industry', 'message', 'status'], [], []],
    ['NotificationPreference', false, ['user_id', 'channel', 'event_type', 'enabled'], [
        'user' => 'User',
    ], ['enabled' => 'boolean']],
    ['Integration', true, ['name', 'slug', 'driver', 'status', 'config', 'last_synced_at', 'last_error'], [], [
        'config' => 'encrypted:array', 'last_synced_at' => 'datetime',
    ]],
    ['WebhookEndpoint', true, ['name', 'url', 'secret', 'events', 'is_active'], [
        'deliveries' => ['WebhookDelivery', 'hasMany', 'webhook_endpoint_id'],
    ], ['events' => 'array', 'is_active' => 'boolean']],
    ['WebhookDelivery', false, [
        'webhook_endpoint_id', 'event', 'payload', 'response_code', 'response_body', 'status', 'attempts', 'next_retry_at',
    ], [
        'endpoint' => ['WebhookEndpoint', 'webhook_endpoint_id'],
    ], ['payload' => 'array', 'next_retry_at' => 'datetime']],
    ['SupplierInvoice', true, [
        'relationship_id', 'purchase_order_id', 'invoice_number', 'status', 'currency_code', 'subtotal', 'tax_total', 'grand_total',
        'issued_at', 'due_at', 'paid_at', 'notes',
    ], [
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'lines' => ['SupplierInvoiceLine', 'hasMany', 'supplier_invoice_id'],
        'payments' => ['PaymentRecord', 'hasMany', 'supplier_invoice_id'],
    ], [
        'subtotal' => 'decimal:4', 'tax_total' => 'decimal:4', 'grand_total' => 'decimal:4',
        'issued_at' => 'datetime', 'due_at' => 'datetime', 'paid_at' => 'datetime',
    ]],
    ['SupplierInvoiceLine', false, [
        'supplier_invoice_id', 'product_id', 'description', 'quantity', 'unit_price', 'line_total',
    ], [
        'supplierInvoice' => ['SupplierInvoice', 'supplier_invoice_id'],
        'product' => 'Product',
    ], ['quantity' => 'decimal:4', 'unit_price' => 'decimal:4', 'line_total' => 'decimal:4']],
    ['PaymentRecord', true, [
        'supplier_invoice_id', 'amount', 'currency_code', 'method', 'reference', 'paid_at', 'recorded_by', 'notes',
    ], [
        'supplierInvoice' => ['SupplierInvoice', 'supplier_invoice_id'],
        'recorder' => ['User', 'recorded_by'],
    ], ['amount' => 'decimal:4', 'paid_at' => 'datetime']],
    ['ConsignmentAgreement', true, [
        'relationship_id', 'name', 'status', 'starts_at', 'ends_at', 'terms',
    ], [
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'balances' => ['ConsignmentBalance', 'hasMany', 'agreement_id'],
        'movements' => ['ConsignmentMovement', 'hasMany', 'agreement_id'],
    ], ['starts_at' => 'datetime', 'ends_at' => 'datetime']],
    ['ConsignmentBalance', false, [
        'agreement_id', 'warehouse_id', 'product_id', 'quantity', 'unit_cost',
    ], [
        'agreement' => ['ConsignmentAgreement', 'agreement_id'],
        'warehouse' => 'Warehouse',
        'product' => 'Product',
    ], ['quantity' => 'decimal:4', 'unit_cost' => 'decimal:4']],
    ['ConsignmentMovement', false, [
        'agreement_id', 'product_id', 'warehouse_id', 'type', 'quantity', 'reference_type', 'reference_id', 'occurred_at',
    ], [
        'agreement' => ['ConsignmentAgreement', 'agreement_id'],
        'product' => 'Product',
        'warehouse' => 'Warehouse',
    ], ['quantity' => 'decimal:4', 'occurred_at' => 'datetime']],
    ['QualityInspection', true, [
        'goods_receipt_id', 'product_id', 'status', 'result', 'inspector_id', 'inspected_at', 'notes', 'checklist',
    ], [
        'goodsReceipt' => ['GoodsReceipt', 'goods_receipt_id'],
        'product' => 'Product',
        'inspector' => ['User', 'inspector_id'],
    ], ['inspected_at' => 'datetime', 'checklist' => 'array']],
    ['ReturnRequest', true, [
        'relationship_id', 'purchase_order_id', 'status', 'reason', 'requested_by', 'approved_by', 'notes',
    ], [
        'relationship' => ['BuyerSupplierRelationship', 'relationship_id'],
        'purchaseOrder' => ['PurchaseOrder', 'purchase_order_id'],
        'requester' => ['User', 'requested_by'],
        'approver' => ['User', 'approved_by'],
        'lines' => ['ReturnLine', 'hasMany', 'return_request_id'],
    ], []],
    ['ReturnLine', false, ['return_request_id', 'product_id', 'quantity', 'reason'], [
        'returnRequest' => ['ReturnRequest', 'return_request_id'],
        'product' => 'Product',
    ], ['quantity' => 'decimal:4']],
    ['ScheduledTaskRun', false, [
        'task_name', 'status', 'started_at', 'completed_at', 'error', 'meta',
    ], [], ['started_at' => 'datetime', 'completed_at' => 'datetime', 'meta' => 'array']],
];

foreach ($definitions as [$class, $tenant, $fillable, $relations, $casts]) {
    $uses = [
        'use Illuminate\Database\Eloquent\Model;',
        'use Illuminate\Database\Eloquent\Relations\BelongsTo;',
        'use Illuminate\Database\Eloquent\Relations\HasMany;',
    ];
    if ($tenant) {
        array_unshift($uses, 'use App\Models\Concerns\BelongsToTenant;');
    }

    $trait = $tenant ? "\n    use BelongsToTenant;\n" : "\n";

    $fillableStr = implode(",\n        ", array_map(fn ($f) => "'{$f}'", $fillable));
    if ($tenant) {
        $fillableStr = "'tenant_id',\n        ".$fillableStr;
    }

    $castLines = [];
    foreach ($casts as $key => $type) {
        if (str_contains($type, '\\')) {
            $castLines[] = "            '{$key}' => {$type}::class,";
        } else {
            $castLines[] = "            '{$key}' => '{$type}',";
        }
    }
    $castsBlock = $castLines ? "    protected function casts(): array\n    {\n        return [\n".implode("\n", $castLines)."\n        ];\n    }\n\n" : '';

    $relationMethods = '';
    foreach ($relations as $name => $def) {
        if (is_string($def)) {
            $def = [$def, $name.'_id'];
        }

        $related = $def[0];

        if (($def[1] ?? null) === 'hasMany') {
            $foreignKey = $def[2];
            $relationMethods .= "    public function {$name}(): HasMany\n    {\n        return \$this->hasMany({$related}::class, '{$foreignKey}');\n    }\n\n";

            continue;
        }

        $foreignKey = $def[1];
        $relationMethods .= "    public function {$name}(): BelongsTo\n    {\n        return \$this->belongsTo({$related}::class, '{$foreignKey}');\n    }\n\n";
    }

    $useBlock = implode("\n", array_unique($uses));
    $content = <<<PHP
<?php

namespace App\Models;

{$useBlock}

class {$class} extends Model
{{$trait}
    protected \$fillable = [
        {$fillableStr},
    ];

{$castsBlock}{$relationMethods}}
PHP;

    file_put_contents("{$base}/{$class}.php", $content);
}

echo 'Generated '.count($definitions)." models\n";
