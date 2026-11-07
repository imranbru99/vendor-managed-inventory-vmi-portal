<?php

namespace App\Enums;

enum GoodsReceiptStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Partial = 'partial';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
}
