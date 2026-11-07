<?php

namespace App\Console\Commands;

use App\Console\Concerns\LogsScheduledTask;
use App\Models\InventoryBalance;
use App\Models\VmiPolicy;
use App\Services\Replenishment\ReplenishmentEngine;
use Illuminate\Console\Command;

class DetectLowStockCommand extends Command
{
    use LogsScheduledTask;

    protected $signature = 'vmi:low-stock-detect';

    protected $description = 'Detect inventory balances below VMI minimum thresholds';

    public function handle(ReplenishmentEngine $engine): int
    {
        $run = $this->beginScheduledTask('vmi:low-stock-detect');
        $lowStock = 0;

        try {
            VmiPolicy::query()
                ->where('is_active', true)
                ->whereNotNull('min_stock')
                ->each(function (VmiPolicy $policy) use ($engine, &$lowStock): void {
                    $balance = InventoryBalance::query()
                        ->where('tenant_id', $policy->tenant_id)
                        ->where('product_id', $policy->product_id)
                        ->where('warehouse_id', $policy->warehouse_id)
                        ->whereNull('location_id')
                        ->first();

                    if ($balance === null) {
                        return;
                    }

                    $position = $engine->calculateInventoryPosition($balance);
                    if ($position < (float) $policy->min_stock) {
                        $lowStock++;
                    }
                });

            $this->completeScheduledTask($run, ['low_stock_count' => $lowStock]);
            $this->info("Low stock SKUs detected: {$lowStock}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->failScheduledTask($run, $e, ['low_stock_count' => $lowStock]);
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
