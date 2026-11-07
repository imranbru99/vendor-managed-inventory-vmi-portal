<?php

namespace App\Enums;

enum ReplenishmentPolicyType: string
{
    case MinMax = 'min_max';
    case ReorderPoint = 'reorder_point';
    case TargetDaysOfSupply = 'target_days_of_supply';
    case DaysOfSupply = 'days_of_supply';
    case SafetyStock = 'safety_stock';
    case Consumption = 'consumption';
    case Scheduled = 'scheduled';
    case Manual = 'manual';
    case Hybrid = 'hybrid';
    case FixedQuantity = 'fixed_quantity';
    case DemandForecast = 'demand_forecast';
}
