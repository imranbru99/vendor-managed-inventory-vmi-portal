<?php

use App\Enums\OrganizationType;
use App\Enums\ReplenishmentPolicyType;
use App\Models\DemandEvent;
use App\Models\InventoryBalance;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\VmiPolicy;
use App\Models\Warehouse;
use App\Services\Replenishment\ReplenishmentEngine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

test('inventory position sums sellable inbound and subtracts commitments', function (): void {
    $engine = app(ReplenishmentEngine::class);

    $balance = new InventoryBalance([
        'sellable' => 100,
        'reserved' => 15,
        'allocated' => 5,
        'confirmed_inbound' => 20,
    ]);

    expect($engine->calculateInventoryPosition($balance))->toBe(100.0);
});

test('apply order constraints enforces moq and order multiples', function (): void {
    $engine = app(ReplenishmentEngine::class);
    $method = new ReflectionMethod($engine, 'applyOrderConstraints');
    $method->setAccessible(true);

    expect($method->invoke($engine, 5.0, 10.0, 2.0, null))->toBe(10.0);
    expect($method->invoke($engine, 13.0, 0.0, 6.0, null))->toBe(18.0);
    expect($method->invoke($engine, 0.0, 10.0, 1.0, null))->toBe(0.0);
});

test('min-max policy recommends quantity up to max stock', function (): void {
    $engine = app(ReplenishmentEngine::class);
    $method = new ReflectionMethod($engine, 'minMax');
    $method->setAccessible(true);

    $policy = new VmiPolicy([
        'min_stock' => 50,
        'max_stock' => 200,
    ]);

    [$qty, , $target] = $method->invoke($engine, $policy, 80.0);

    expect($qty)->toBe(120.0)
        ->and($target)->toBe(200.0);
});

test('average daily consumption uses demand events in lookback window', function (): void {
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'slug' => 'test-tenant-'.uniqid(),
        'status' => 'active',
    ]);

    $buyer = Organization::query()->create([
        'tenant_id' => $tenant->id,
        'type' => OrganizationType::Buyer,
        'name' => 'Buyer Org',
        'slug' => 'buyer-'.uniqid(),
        'status' => 'active',
    ]);

    $warehouse = Warehouse::query()->create([
        'tenant_id' => $tenant->id,
        'organization_id' => $buyer->id,
        'code' => 'WH1',
        'name' => 'Test Warehouse',
    ]);

    $product = Product::query()->create([
        'tenant_id' => $tenant->id,
        'organization_id' => $buyer->id,
        'sku' => 'SKU-'.uniqid(),
        'name' => 'Test Product',
        'slug' => 'test-product-'.uniqid(),
    ]);

    DemandEvent::query()->create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'event_date' => Carbon::today(),
        'quantity' => 30,
    ]);

    DemandEvent::query()->create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'event_date' => Carbon::today()->subDays(40),
        'quantity' => 300,
    ]);

    $engine = app(ReplenishmentEngine::class);
    $adc = $engine->averageDailyConsumption($tenant->id, $product->id, $warehouse->id, 30);

    expect($adc)->toBe(1.0);
});

test('policy type enum includes min max for replenishment flows', function (): void {
    expect(ReplenishmentPolicyType::MinMax->value)->toBe('min_max');
});
