<?php

namespace App\Services\Inventory;

use App\Enums\InventoryTransactionType;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function adjustStock(
        Product $product,
        Warehouse $warehouse,
        float $qty,
        InventoryTransactionType|string $type,
        array $meta = [],
        ?int $locationId = null,
    ): InventoryTransaction {
        $type = $type instanceof InventoryTransactionType ? $type : InventoryTransactionType::from($type);

        return DB::transaction(function () use ($product, $warehouse, $qty, $type, $meta, $locationId): InventoryTransaction {
            $balance = $this->lockBalance($product, $warehouse, $locationId);

            $sellableBefore = (float) $balance->sellable;

            $this->applyBalanceChange($balance, $qty, $type, $meta);

            $balance->save();

            return InventoryTransaction::query()->create([
                'tenant_id' => $balance->tenant_id,
                'warehouse_id' => $warehouse->id,
                'location_id' => $locationId,
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => abs($qty),
                'quantity_before' => $sellableBefore,
                'quantity_after' => (float) $balance->sellable,
                'unit_cost' => $meta['unit_cost'] ?? $balance->average_cost,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id' => $meta['reference_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
                'performed_by' => $meta['performed_by'] ?? Auth::id(),
                'occurred_at' => $meta['occurred_at'] ?? now(),
                'meta' => $meta['meta'] ?? null,
            ]);
        });
    }

    public function reserve(Product $product, Warehouse $warehouse, float $qty, array $meta = [], ?int $locationId = null): InventoryTransaction
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Reserve quantity must be positive.');
        }

        return $this->adjustStock($product, $warehouse, $qty, InventoryTransactionType::Reservation, $meta, $locationId);
    }

    public function release(Product $product, Warehouse $warehouse, float $qty, array $meta = [], ?int $locationId = null): InventoryTransaction
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Release quantity must be positive.');
        }

        $meta['direction'] = 'release';

        return $this->adjustStock($product, $warehouse, -$qty, InventoryTransactionType::Reservation, $meta, $locationId);
    }

    public function allocate(Product $product, Warehouse $warehouse, float $qty, array $meta = [], ?int $locationId = null): InventoryTransaction
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Allocate quantity must be positive.');
        }

        return $this->adjustStock($product, $warehouse, $qty, InventoryTransactionType::Allocation, $meta, $locationId);
    }

    public function deallocate(Product $product, Warehouse $warehouse, float $qty, array $meta = [], ?int $locationId = null): InventoryTransaction
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Deallocate quantity must be positive.');
        }

        $meta['direction'] = 'release';

        return $this->adjustStock($product, $warehouse, -$qty, InventoryTransactionType::Allocation, $meta, $locationId);
    }

    public function receive(Product $product, Warehouse $warehouse, float $qty, array $meta = [], ?int $locationId = null): InventoryTransaction
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Receipt quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $warehouse, $qty, $meta, $locationId): InventoryTransaction {
            $transaction = $this->adjustStock($product, $warehouse, $qty, InventoryTransactionType::Receipt, $meta, $locationId);

            $balance = $this->lockBalance($product, $warehouse, $locationId);
            $inbound = max(0, (float) $balance->confirmed_inbound - $qty);
            $balance->confirmed_inbound = $inbound;
            $balance->save();

            return $transaction;
        });
    }

    protected function lockBalance(Product $product, Warehouse $warehouse, ?int $locationId = null): InventoryBalance
    {
        $query = InventoryBalance::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($locationId === null, fn ($q) => $q->whereNull('location_id'))
            ->lockForUpdate();

        $balance = $query->first();

        if ($balance === null) {
            $balance = InventoryBalance::query()->create([
                'tenant_id' => $product->tenant_id,
                'warehouse_id' => $warehouse->id,
                'location_id' => $locationId,
                'product_id' => $product->id,
            ]);

            return InventoryBalance::query()->whereKey($balance->id)->lockForUpdate()->firstOrFail();
        }

        return $balance;
    }

    protected function applyBalanceChange(InventoryBalance $balance, float $qty, InventoryTransactionType $type, array $meta = []): void
    {
        $direction = $qty >= 0 ? 1 : -1;
        $amount = abs($qty);
        $allocationForward = ($meta['direction'] ?? null) !== 'release';

        match ($type) {
            InventoryTransactionType::Receipt, InventoryTransactionType::TransferIn, InventoryTransactionType::Return => $this->incrementSellable($balance, $amount),
            InventoryTransactionType::Issue, InventoryTransactionType::TransferOut => $this->decrementSellable($balance, $amount),
            InventoryTransactionType::Adjustment => $direction > 0
                ? $this->incrementSellable($balance, $amount)
                : $this->decrementSellable($balance, $amount),
            InventoryTransactionType::Reservation => $this->reserveQty($balance, $amount, $qty >= 0),
            InventoryTransactionType::Allocation => $this->allocateQty($balance, $amount, $allocationForward && $qty >= 0),
            InventoryTransactionType::Quarantine => $this->moveBucket($balance, 'sellable', 'quarantined', $amount, $qty >= 0),
            InventoryTransactionType::Damage => $this->moveBucket($balance, 'sellable', 'damaged', $amount, $qty >= 0),
            InventoryTransactionType::CycleCount => $direction > 0
                ? $this->incrementSellable($balance, $amount)
                : $this->decrementSellable($balance, $amount),
        };

        $balance->on_hand = (float) $balance->sellable
            + (float) $balance->reserved
            + (float) $balance->allocated
            + (float) $balance->quarantined
            + (float) $balance->damaged;
    }

    protected function reserveQty(InventoryBalance $balance, float $amount, bool $reserve): void
    {
        if ($reserve) {
            if ((float) $balance->sellable < $amount) {
                throw new InvalidArgumentException('Insufficient sellable quantity to reserve.');
            }
            $balance->sellable = (float) $balance->sellable - $amount;
            $balance->reserved = (float) $balance->reserved + $amount;
        } else {
            if ((float) $balance->reserved < $amount) {
                throw new InvalidArgumentException('Insufficient reserved quantity to release.');
            }
            $balance->reserved = (float) $balance->reserved - $amount;
            $balance->sellable = (float) $balance->sellable + $amount;
        }
    }

    protected function allocateQty(InventoryBalance $balance, float $amount, bool $allocate): void
    {
        if ($allocate) {
            if ((float) $balance->sellable < $amount) {
                throw new InvalidArgumentException('Insufficient sellable quantity to allocate.');
            }
            $balance->sellable = (float) $balance->sellable - $amount;
            $balance->allocated = (float) $balance->allocated + $amount;
        } else {
            if ((float) $balance->allocated < $amount) {
                throw new InvalidArgumentException('Insufficient allocated quantity to deallocate.');
            }
            $balance->allocated = (float) $balance->allocated - $amount;
            $balance->sellable = (float) $balance->sellable + $amount;
        }
    }

    protected function incrementSellable(InventoryBalance $balance, float $amount): void
    {
        $balance->sellable = (float) $balance->sellable + $amount;
    }

    protected function decrementSellable(InventoryBalance $balance, float $amount): void
    {
        if ((float) $balance->sellable < $amount) {
            throw new InvalidArgumentException('Insufficient sellable quantity.');
        }
        $balance->sellable = (float) $balance->sellable - $amount;
    }

    protected function moveBucket(InventoryBalance $balance, string $from, string $to, float $amount, bool $forward): void
    {
        if ($forward) {
            if ((float) $balance->{$from} < $amount) {
                throw new InvalidArgumentException("Insufficient {$from} quantity.");
            }
            $balance->{$from} = (float) $balance->{$from} - $amount;
            $balance->{$to} = (float) $balance->{$to} + $amount;
        } else {
            if ((float) $balance->{$to} < $amount) {
                throw new InvalidArgumentException("Insufficient {$to} quantity.");
            }
            $balance->{$to} = (float) $balance->{$to} - $amount;
            $balance->{$from} = (float) $balance->{$from} + $amount;
        }
    }
}
