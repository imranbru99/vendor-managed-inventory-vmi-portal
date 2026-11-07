<?php

namespace App\Console\Commands;

use App\Console\Concerns\LogsScheduledTask;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Console\Command;

class MonitorOverduePurchaseOrdersCommand extends Command
{
    use LogsScheduledTask;

    protected $signature = 'vmi:overdue-po-monitor';

    protected $description = 'Monitor purchase orders past required delivery date';

    public function handle(): int
    {
        $run = $this->beginScheduledTask('vmi:overdue-po-monitor');

        try {
            $overdue = PurchaseOrder::query()
                ->whereNotNull('required_delivery_date')
                ->whereDate('required_delivery_date', '<', now()->toDateString())
                ->whereNotIn('status', [
                    PurchaseOrderStatus::Received,
                    PurchaseOrderStatus::Cancelled,
                    PurchaseOrderStatus::Closed,
                ])
                ->count();

            $this->completeScheduledTask($run, ['overdue_count' => $overdue]);
            $this->info("Overdue purchase orders: {$overdue}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->failScheduledTask($run, $e);
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
