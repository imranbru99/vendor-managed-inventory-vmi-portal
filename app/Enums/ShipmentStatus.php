<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Picking = 'picking';
    case Packed = 'packed';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Exception = 'exception';
}
