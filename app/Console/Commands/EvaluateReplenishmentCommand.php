<?php

namespace App\Console\Commands;

use App\Console\Concerns\LogsScheduledTask;
use App\Enums\RelationshipStatus;
use App\Models\BuyerSupplierRelationship;
use App\Services\Replenishment\ReplenishmentEngine;
use Illuminate\Console\Command;

class EvaluateReplenishmentCommand extends Command
{
    use LogsScheduledTask;

    protected $signature = 'vmi:replenishment-evaluate';

    protected $description = 'Evaluate replenishment policies for approved buyer-supplier relationships';

    public function handle(ReplenishmentEngine $engine): int
    {
        $run = $this->beginScheduledTask('vmi:replenishment-evaluate');
        $processed = 0;

        try {
            BuyerSupplierRelationship::query()
                ->where('status', RelationshipStatus::Approved)
                ->orderBy('id')
                ->each(function (BuyerSupplierRelationship $relationship) use ($engine, &$processed): void {
                    $engine->runForRelationship($relationship, 'scheduler');
                    $processed++;
                });

            $this->completeScheduledTask($run, ['relationships_processed' => $processed]);
            $this->info("Replenishment evaluation completed for {$processed} relationships.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->failScheduledTask($run, $e, ['relationships_processed' => $processed]);
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
