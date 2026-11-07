<?php

namespace App\Enums;

enum OrganizationType: string
{
    case Buyer = 'buyer';
    case Supplier = 'supplier';
    case Both = 'both';
    case Internal = 'internal';
}
