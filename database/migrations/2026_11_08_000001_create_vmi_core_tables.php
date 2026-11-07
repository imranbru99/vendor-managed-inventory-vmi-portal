<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('slug');
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('status')->default('pending');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('organization_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_slug');
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('active');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('buyer_supplier_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('supplier_organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['buyer_organization_id', 'supplier_organization_id'], 'bsr_buyer_supplier_unique');
        });

        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('category')->default('core');
            $table->string('version')->default('1.0.0');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_global_active')->default(true);
            $table->json('dependencies')->nullable();
            $table->json('permissions')->nullable();
            $table->json('settings_schema')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->json('settings')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'module_id']);
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->string('currency_code', 3)->default('USD');
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type')->default('standard');
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('zone')->nullable();
            $table->string('aisle')->nullable();
            $table->string('bin')->nullable();
            $table->string('type')->default('standard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('units_of_measure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type')->default('unit');
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->string('gtin')->nullable();
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->foreignId('base_uom_id')->nullable()->constrained('units_of_measure')->nullOnDelete();
            $table->unsignedInteger('case_quantity')->nullable();
            $table->decimal('weight', 12, 4)->nullable();
            $table->string('weight_unit')->nullable();
            $table->decimal('length', 12, 4)->nullable();
            $table->decimal('width', 12, 4)->nullable();
            $table->decimal('height', 12, 4)->nullable();
            $table->string('dimension_unit')->nullable();
            $table->string('tax_category')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('shelf_life_days')->nullable();
            $table->text('storage_requirements')->nullable();
            $table->json('specifications')->nullable();
            $table->string('image_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'sku']);
        });

        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->decimal('unit_price', 12, 4)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('moq', 12, 4)->nullable();
            $table->decimal('order_multiple', 12, 4)->nullable();
            $table->decimal('max_order_qty', 12, 4)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['relationship_id', 'product_id']);
        });

        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('on_hand', 16, 4)->default(0);
            $table->decimal('sellable', 16, 4)->default(0);
            $table->decimal('reserved', 16, 4)->default(0);
            $table->decimal('allocated', 16, 4)->default(0);
            $table->decimal('quarantined', 16, 4)->default(0);
            $table->decimal('damaged', 16, 4)->default(0);
            $table->decimal('in_transit', 16, 4)->default(0);
            $table->decimal('confirmed_inbound', 16, 4)->default(0);
            $table->decimal('consignment', 16, 4)->default(0);
            $table->decimal('returned', 16, 4)->default(0);
            $table->decimal('average_cost', 16, 4)->nullable();
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'location_id', 'product_id'], 'inv_bal_wh_loc_prod_unique');
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('quantity', 16, 4);
            $table->decimal('quantity_before', 16, 4)->nullable();
            $table->decimal('quantity_after', 16, 4)->nullable();
            $table->decimal('unit_cost', 16, 4)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('vmi_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained('buyer_supplier_relationships')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('terms')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('vmi_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('agreement_id')->constrained('vmi_agreements')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('policy_type');
            $table->decimal('min_stock', 16, 4)->nullable();
            $table->decimal('max_stock', 16, 4)->nullable();
            $table->decimal('safety_stock', 16, 4)->nullable();
            $table->decimal('reorder_point', 16, 4)->nullable();
            $table->unsignedInteger('target_days_of_supply')->nullable();
            $table->unsignedInteger('replenishment_frequency_days')->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->decimal('service_level_target', 5, 4)->nullable();
            $table->decimal('moq', 16, 4)->nullable();
            $table->decimal('order_multiple', 16, 4)->nullable();
            $table->decimal('max_order_qty', 16, 4)->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->decimal('auto_approve_max_qty', 16, 4)->nullable();
            $table->decimal('auto_approve_max_value', 16, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->string('type');
            $table->string('title');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('vmi_policies');
        Schema::dropIfExists('vmi_agreements');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units_of_measure');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('warehouse_locations');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('tenant_modules');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('buyer_supplier_relationships');
        Schema::dropIfExists('organization_memberships');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('tenants');
    }
};
