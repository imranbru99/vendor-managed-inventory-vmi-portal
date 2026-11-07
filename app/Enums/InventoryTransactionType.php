<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case Receipt = 'receipt';
    case Issue = 'issue';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Reservation = 'reservation';
    case Allocation = 'allocation';
    case Quarantine = 'quarantine';
    case Damage = 'damage';
    case Return = 'return';
    case CycleCount = 'cycle_count';
}
