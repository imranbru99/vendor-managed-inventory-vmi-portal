<?php

namespace App\Enums;

enum ModuleCategory: string
{
    case Core = 'core';
    case Operations = 'operations';
    case Commerce = 'commerce';
    case Analytics = 'analytics';
    case Integration = 'integration';
    case Support = 'support';
}
