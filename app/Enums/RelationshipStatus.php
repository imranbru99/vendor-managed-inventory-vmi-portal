<?php

namespace App\Enums;

enum RelationshipStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Suspended = 'suspended';
    case Terminated = 'terminated';
}
