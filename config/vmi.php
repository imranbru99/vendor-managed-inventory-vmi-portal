<?php

return [

    'demo' => [
        'password' => env('DEMO_PASSWORD', env('VMI_DEMO_ADMIN_PASSWORD', 'password')),
        'tenant_slug' => env('VMI_DEMO_TENANT_SLUG', 'northstar-retail'),
        'admin_email' => env('VMI_DEMO_ADMIN_EMAIL', 'platform@vmi.test'),
        'admin_password' => env('VMI_DEMO_ADMIN_PASSWORD', env('DEMO_PASSWORD', 'password')),
        'buyer_email' => env('VMI_DEMO_BUYER_EMAIL', 'buyer.admin@vmi.test'),
        'supplier_email' => env('VMI_DEMO_SUPPLIER_EMAIL', 'supplier.admin@vmi.test'),
    ],

    'features' => [
        'multi_tenancy' => env('VMI_FEATURE_MULTI_TENANCY', true),
        'vmi_agreements' => env('VMI_FEATURE_VMI_AGREEMENTS', true),
        'auto_replenishment' => env('VMI_FEATURE_AUTO_REPLENISHMENT', false),
        'ai_assist' => env('VMI_FEATURE_AI_ASSIST', false),
        'edi_integrations' => env('VMI_FEATURE_EDI', false),
    ],

    'inventory' => [
        'stale_threshold_hours' => (int) env('VMI_INVENTORY_STALE_THRESHOLD_HOURS', 24),
        'stale_hours' => (int) env('VMI_INVENTORY_STALE_HOURS', env('VMI_INVENTORY_STALE_THRESHOLD_HOURS', 24)),
    ],

    'replenishment' => [
        'demand_lookback_days' => (int) env('VMI_REPLENISHMENT_DEMAND_LOOKBACK_DAYS', 30),
    ],

    'pricing' => [
        'plans' => [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For teams piloting vendor-managed inventory with a single site.',
                'price_monthly' => 299,
                'price_yearly' => 2990,
                'currency_code' => 'USD',
                'features' => ['Up to 3 suppliers', 'VMI agreements', 'Inventory visibility', 'Email support'],
                'highlight' => false,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Multi-location replenishment with supplier collaboration.',
                'price_monthly' => 799,
                'price_yearly' => 7990,
                'currency_code' => 'USD',
                'features' => ['Unlimited suppliers', 'Auto replenishment', 'Procurement & POs', 'Priority support'],
                'highlight' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Warehouse, logistics, and consignment for complex networks.',
                'price_monthly' => 1499,
                'price_yearly' => 14990,
                'currency_code' => 'USD',
                'features' => ['Advanced modules', 'Integrations hub', 'Forecasting', 'Dedicated CSM'],
                'highlight' => false,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Global rollouts with SSO, custom SLAs, and private cloud options.',
                'price_monthly' => null,
                'price_yearly' => null,
                'currency_code' => 'USD',
                'features' => ['SSO & SCIM', 'Custom contracts', 'AI & analytics suite', '24/7 support'],
                'highlight' => false,
            ],
        ],
    ],

];
