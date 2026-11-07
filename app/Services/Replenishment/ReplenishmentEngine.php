<?php

namespace App\Services\Replenishment;

use App\Enums\ReplenishmentPolicyType;
use App\Models\BuyerSupplierRelationship;
use App\Models\DemandEvent;
use App\Models\InventoryBalance;
use App\Models\ReplenishmentRecommendation;
use App\Models\ReplenishmentRun;
use App\Models\SupplierProduct;
use App\Models\VmiAgreement;
use App\Models\VmiPolicy;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReplenishmentEngine
{
    public function runForRelationship(BuyerSupplierRelationship $relationship, ?string $triggeredBy = null): ReplenishmentRun
    {
        return DB::transaction(function () use ($relationship, $triggeredBy): ReplenishmentRun {
            $tenantId = $this->resolveTenantId($relationship);

            $run = ReplenishmentRun::query()->create([
                'tenant_id' => $tenantId,
                'relationship_id' => $relationship->id,
                'status' => 'running',
                'started_at' => now(),
                'triggered_by' => $triggeredBy ?? 'system',
            ]);

            $policies = $this->policiesForRelationship($relationship);
            $recommendations = [];

            foreach ($policies as $policy) {
                $recommendations[] = $this->buildRecommendation($run, $policy, $relationship);
            }

            $run->update([
                'status' => 'completed',
                'completed_at' => now(),
                'summary' => [
                    'policy_count' => $policies->count(),
                    'recommendation_count' => count($recommendations),
                    'total_recommended_qty' => collect($recommendations)->sum('recommended_qty'),
                ],
            ]);

            return $run->fresh(['recommendations']);
        });
    }

    public function calculateInventoryPosition(InventoryBalance $balance): float
    {
        $committed = (float) $balance->reserved + (float) $balance->allocated;

        return (float) $balance->sellable + (float) $balance->confirmed_inbound - $committed;
    }

    public function averageDailyConsumption(int $tenantId, int $productId, int $warehouseId, ?int $days = null): float
    {
        $days = $days ?? (int) config('vmi.replenishment.demand_lookback_days', 30);
        $from = Carbon::today()->subDays($days);

        $total = DemandEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('event_date', '>=', $from)
            ->sum('quantity');

        return $days > 0 ? round((float) $total / $days, 4) : 0.0;
    }

    protected function buildRecommendation(ReplenishmentRun $run, VmiPolicy $policy, BuyerSupplierRelationship $relationship): ReplenishmentRecommendation
    {
        $balance = InventoryBalance::query()
            ->where('tenant_id', $policy->tenant_id)
            ->where('product_id', $policy->product_id)
            ->where('warehouse_id', $policy->warehouse_id)
            ->whereNull('location_id')
            ->first();

        $position = $balance ? $this->calculateInventoryPosition($balance) : 0.0;
        $adc = $this->averageDailyConsumption($policy->tenant_id, $policy->product_id, $policy->warehouse_id);
        $staleHours = (int) config('vmi.inventory.stale_hours', config('vmi.inventory.stale_threshold_hours', 24));
        $dataAsOf = $balance?->updated_at ?? now();
        $isStale = $dataAsOf->lt(now()->subHours($staleHours));

        $supplierProduct = SupplierProduct::query()
            ->where('relationship_id', $relationship->id)
            ->where('product_id', $policy->product_id)
            ->first();

        [$rawQty, $explanation, $targetStock] = $this->recommendedQuantity($policy, $position, $adc);

        $moq = (float) ($policy->moq ?? $supplierProduct?->moq ?? 0);
        $multiple = (float) ($policy->order_multiple ?? $supplierProduct?->order_multiple ?? 1);
        $maxOrder = $policy->max_order_qty ?? $supplierProduct?->max_order_qty;

        $recommendedQty = $this->applyOrderConstraints($rawQty, $moq, $multiple, $maxOrder !== null ? (float) $maxOrder : null);

        if ($recommendedQty > 0) {
            $explanation .= sprintf(
                ' Applied constraints: MOQ %s, multiple %s%s.',
                $moq,
                $multiple,
                $maxOrder !== null ? ", max {$maxOrder}" : ''
            );
        } else {
            $explanation .= ' No replenishment required after constraints.';
        }

        $leadDays = $policy->lead_time_days ?? $supplierProduct?->lead_time_days ?? 7;
        $daysOfInventory = $adc > 0 ? round($position / $adc, 4) : null;

        return ReplenishmentRecommendation::query()->create([
            'tenant_id' => $policy->tenant_id,
            'run_id' => $run->id,
            'policy_id' => $policy->id,
            'product_id' => $policy->product_id,
            'warehouse_id' => $policy->warehouse_id,
            'relationship_id' => $relationship->id,
            'inventory_position' => $position,
            'average_daily_consumption' => $adc,
            'safety_stock' => $policy->safety_stock,
            'reorder_point' => $policy->reorder_point,
            'target_stock' => $targetStock,
            'recommended_qty' => $recommendedQty,
            'expected_delivery_date' => now()->addDays($leadDays)->toDateString(),
            'days_of_inventory' => $daysOfInventory,
            'explanation' => $explanation,
            'data_as_of' => $dataAsOf,
            'is_stale' => $isStale,
        ]);
    }

    /**
     * @return array{0: float, 1: string, 2: ?float}
     */
    protected function recommendedQuantity(VmiPolicy $policy, float $position, float $adc): array
    {
        $type = $policy->policy_type;
        $typeValue = $type instanceof ReplenishmentPolicyType ? $type->value : (string) $type;

        return match ($typeValue) {
            ReplenishmentPolicyType::MinMax->value => $this->minMax($policy, $position),
            ReplenishmentPolicyType::ReorderPoint->value => $this->reorderPoint($policy, $position),
            ReplenishmentPolicyType::TargetDaysOfSupply->value,
            ReplenishmentPolicyType::DaysOfSupply->value => $this->daysOfSupply($policy, $position, $adc),
            ReplenishmentPolicyType::SafetyStock->value => $this->safetyStock($policy, $position),
            ReplenishmentPolicyType::Consumption->value,
            ReplenishmentPolicyType::DemandForecast->value => $this->consumptionPolicy($policy, $position, $adc),
            ReplenishmentPolicyType::Scheduled->value => $this->scheduledPolicy($policy, $position),
            ReplenishmentPolicyType::Manual->value => $this->manualPolicy($policy, $position),
            ReplenishmentPolicyType::Hybrid->value => $this->hybridPolicy($policy, $position, $adc),
            ReplenishmentPolicyType::FixedQuantity->value => [
                (float) ($policy->settings['fixed_qty'] ?? $policy->max_stock ?? 0),
                'Fixed quantity policy applied.',
                $policy->max_stock,
            ],
            default => [0.0, 'Unsupported policy type.', null],
        };
    }

    protected function minMax(VmiPolicy $policy, float $position): array
    {
        $max = (float) ($policy->max_stock ?? 0);
        $min = (float) ($policy->min_stock ?? 0);
        $qty = max(0, $max - $position);

        return [$qty, "Min/max: position {$position}, min {$min}, max {$max}.", $max];
    }

    protected function reorderPoint(VmiPolicy $policy, float $position): array
    {
        $rop = (float) ($policy->reorder_point ?? 0);
        $max = (float) ($policy->max_stock ?? $rop * 2);
        $qty = $position <= $rop ? max(0, $max - $position) : 0.0;

        return [$qty, "Reorder point {$rop}: position {$position}.", $max];
    }

    protected function daysOfSupply(VmiPolicy $policy, float $position, float $adc): array
    {
        $targetDays = (int) ($policy->target_days_of_supply ?? 14);
        $target = $adc * $targetDays + (float) ($policy->safety_stock ?? 0);
        $qty = max(0, $target - $position);

        return [$qty, "Days-of-supply target {$targetDays}d at ADC {$adc}.", $target];
    }

    protected function safetyStock(VmiPolicy $policy, float $position): array
    {
        $safety = (float) ($policy->safety_stock ?? 0);
        $target = $safety + (float) ($policy->reorder_point ?? $safety);
        $qty = $position < $target ? max(0, $target - $position) : 0.0;

        return [$qty, "Safety stock floor {$safety}, target {$target}.", $target];
    }

    protected function consumptionPolicy(VmiPolicy $policy, float $position, float $adc): array
    {
        $coverDays = (int) ($policy->settings['cover_days'] ?? $policy->target_days_of_supply ?? 7);
        $target = $adc * $coverDays;
        $qty = max(0, $target - $position);

        return [$qty, "Consumption cover {$coverDays} days at ADC {$adc}.", $target];
    }

    protected function scheduledPolicy(VmiPolicy $policy, float $position): array
    {
        $scheduledQty = (float) ($policy->settings['scheduled_qty'] ?? $policy->max_stock ?? 0);
        $qty = $scheduledQty > 0 ? $scheduledQty : 0.0;

        return [$qty, "Scheduled replenishment qty {$scheduledQty}.", $scheduledQty + $position];
    }

    protected function manualPolicy(VmiPolicy $policy, float $position): array
    {
        $manualQty = (float) ($policy->settings['manual_qty'] ?? 0);

        return [$manualQty, "Manual override quantity {$manualQty}.", $manualQty + $position];
    }

    protected function hybridPolicy(VmiPolicy $policy, float $position, float $adc): array
    {
        [, , $targetA] = $this->daysOfSupply($policy, $position, $adc);
        [, , $targetB] = $this->reorderPoint($policy, $position);
        $target = max((float) $targetA, (float) $targetB);
        $qty = max(0, $target - $position);

        return [$qty, 'Hybrid policy combined days-of-supply and reorder-point targets.', $target];
    }

    protected function applyOrderConstraints(float $qty, float $moq, float $multiple, ?float $maxOrder): float
    {
        if ($qty <= 0) {
            return 0.0;
        }

        if ($moq > 0 && $qty < $moq) {
            $qty = $moq;
        }

        if ($multiple > 1) {
            $qty = ceil($qty / $multiple) * $multiple;
        }

        if ($maxOrder !== null && $qty > $maxOrder) {
            $qty = $maxOrder;
        }

        return round($qty, 4);
    }

    protected function policiesForRelationship(BuyerSupplierRelationship $relationship): Collection
    {
        $agreementIds = VmiAgreement::query()
            ->where('relationship_id', $relationship->id)
            ->pluck('id');

        return VmiPolicy::query()
            ->whereIn('agreement_id', $agreementIds)
            ->where('is_active', true)
            ->get();
    }

    protected function resolveTenantId(BuyerSupplierRelationship $relationship): int
    {
        $agreement = VmiAgreement::query()->where('relationship_id', $relationship->id)->first();

        $relationship->loadMissing('buyerOrganization');

        return (int) ($agreement?->tenant_id ?? $relationship->buyerOrganization?->tenant_id ?? 0);
    }
}
