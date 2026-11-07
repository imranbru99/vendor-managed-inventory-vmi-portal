<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('event_date');
            $table->decimal('quantity', 16, 4);
            $table->string('source')->default('manual');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'product_id', 'warehouse_id', 'event_date'], 'demand_events_lookup_idx');
        });

        Schema::create('replenishment_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->nullable()->constrained('buyer_supplier_relationships')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('triggered_by')->nullable();
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('replenishment_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('run_id')->constrained('replenishment_runs')->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained('vmi_policies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->decimal('inventory_position', 16, 4)->default(0);
            $table->decimal('average_daily_consumption', 16, 4)->nullable();
            $table->decimal('safety_stock', 16, 4)->nullable();
            $table->decimal('reorder_point', 16, 4)->nullable();
            $table->decimal('target_stock', 16, 4)->nullable();
            $table->decimal('recommended_qty', 16, 4)->default(0);
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('days_of_inventory', 12, 4)->nullable();
            $table->text('explanation')->nullable();
            $table->timestamp('data_as_of')->nullable();
            $table->boolean('is_stale')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('replenishment_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->string('proposal_number');
            $table->string('status')->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->decimal('total_qty', 16, 4)->default(0);
            $table->decimal('total_value', 16, 4)->default(0);
            $table->string('currency_code', 3)->default('USD');
            $table->date('requested_delivery_date')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'proposal_number']);
        });

        Schema::create('replenishment_proposal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('replenishment_proposals')->cascadeOnDelete();
            $table->foreignId('recommendation_id')->nullable()->constrained('replenishment_recommendations')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('policy_id')->nullable()->constrained('vmi_policies')->nullOnDelete();
            $table->decimal('recommended_qty', 16, 4)->default(0);
            $table->decimal('proposed_qty', 16, 4)->default(0);
            $table->decimal('approved_qty', 16, 4)->nullable();
            $table->decimal('unit_price', 16, 4)->nullable();
            $table->string('line_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->foreignId('proposal_id')->nullable()->constrained('replenishment_proposals')->nullOnDelete();
            $table->string('po_number');
            $table->string('status')->default('draft');
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('subtotal', 16, 4)->default(0);
            $table->decimal('tax_total', 16, 4)->default(0);
            $table->decimal('discount_total', 16, 4)->default(0);
            $table->decimal('grand_total', 16, 4)->default(0);
            $table->foreignId('delivery_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->date('required_delivery_date')->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('terms')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'po_number']);
        });

        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 16, 4);
            $table->decimal('acknowledged_qty', 16, 4)->nullable();
            $table->decimal('received_qty', 16, 4)->default(0);
            $table->decimal('unit_price', 16, 4)->nullable();
            $table->decimal('discount', 16, 4)->default(0);
            $table->decimal('tax', 16, 4)->default(0);
            $table->decimal('line_total', 16, 4)->default(0);
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('stage')->default('default');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->date('confirmed_delivery_date')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->string('shipment_number');
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('eta')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('shipping_cost', 16, 4)->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'shipment_number']);
        });

        Schema::create('shipment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('purchase_order_line_id')->constrained('purchase_order_lines')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 16, 4);
            $table->timestamps();
        });

        Schema::create('shipment_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('status');
            $table->text('description')->nullable();
            $table->timestamp('occurred_at');
            $table->string('location')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('advance_shipping_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('asn_number');
            $table->string('status')->default('draft');
            $table->timestamp('expected_delivery_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'asn_number']);
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->string('grn_number');
            $table->string('status')->default('draft');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'grn_number']);
        });

        Schema::create('goods_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_line_id')->constrained('purchase_order_lines')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('expected_qty', 16, 4)->default(0);
            $table->decimal('received_qty', 16, 4)->default(0);
            $table->decimal('damaged_qty', 16, 4)->default(0);
            $table->decimal('rejected_qty', 16, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('entity_type');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('stage_order')->default(1);
            $table->string('approver_role')->nullable();
            $table->decimal('amount_threshold', 16, 4)->nullable();
            $table->decimal('quantity_threshold', 16, 4)->nullable();
            $table->boolean('is_auto_approve')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('status')->default('pending');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('locale', 10)->default('en');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('template')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('locale', 10)->default('en');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cover_image')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('category')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('industry')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel');
            $table->string('event_type');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'event_type'], 'notification_prefs_user_channel_event_unique');
        });

        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('driver');
            $table->string('status')->default('inactive');
            $table->json('config')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
            $table->string('event');
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('invoice_number');
            $table->string('status')->default('draft');
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('subtotal', 16, 4)->default(0);
            $table->decimal('tax_total', 16, 4)->default(0);
            $table->decimal('grand_total', 16, 4)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number']);
        });

        Schema::create('supplier_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 16, 4)->default(1);
            $table->decimal('unit_price', 16, 4)->default(0);
            $table->decimal('line_total', 16, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->decimal('amount', 16, 4);
            $table->string('currency_code', 3)->default('USD');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('consignment_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });

        Schema::create('consignment_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('consignment_agreements')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 16, 4)->default(0);
            $table->decimal('unit_cost', 16, 4)->nullable();
            $table->timestamps();

            $table->unique(['agreement_id', 'warehouse_id', 'product_id'], 'consignment_bal_agreement_wh_prod_unique');
        });

        Schema::create('consignment_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('consignment_agreements')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('quantity', 16, 4);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('result')->nullable();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inspected_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('checklist')->nullable();
            $table->timestamps();
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('reason')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained('return_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 16, 4);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_task_runs', function (Blueprint $table) {
            $table->id();
            $table->string('task_name');
            $table->string('status')->default('running');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_runs');
        Schema::dropIfExists('return_lines');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('quality_inspections');
        Schema::dropIfExists('consignment_movements');
        Schema::dropIfExists('consignment_balances');
        Schema::dropIfExists('consignment_agreements');
        Schema::dropIfExists('payment_records');
        Schema::dropIfExists('supplier_invoice_lines');
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('integrations');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('demo_requests');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
        Schema::dropIfExists('goods_receipt_lines');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('advance_shipping_notices');
        Schema::dropIfExists('shipment_tracking_events');
        Schema::dropIfExists('shipment_lines');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('supplier_acknowledgments');
        Schema::dropIfExists('purchase_order_approvals');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('replenishment_proposal_lines');
        Schema::dropIfExists('replenishment_proposals');
        Schema::dropIfExists('replenishment_recommendations');
        Schema::dropIfExists('replenishment_runs');
        Schema::dropIfExists('demand_events');
    }
};
