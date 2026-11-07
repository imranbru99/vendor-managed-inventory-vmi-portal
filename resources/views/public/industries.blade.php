@extends('layouts.public')

@section('title', 'Industries')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Industries we serve',
        'lead' => 'VMI Portal adapts to sector-specific compliance, velocity, and supplier networks.',
    ])

    <section class="mx-auto max-w-7xl px-4 py-16 lg:px-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Manufacturing', 'Component and finished-goods VMI with quality and lot traceability.'],
                ['Distribution', 'Multi-DC replenishment and consignment programs.'],
                ['Retail', 'Store and DC inventory with supplier-managed backstock.'],
                ['Healthcare', 'Controlled replenishment with audit trails.'],
                ['Industrial MRO', 'Slow movers and critical spares with policy-based min/max.'],
                ['Automotive', 'Tiered supplier collaboration and ASN alignment.'],
            ] as [$name, $desc])
                <div class="reveal-on-scroll rounded-xl border border-slate-200 p-6 dark:border-slate-700">
                    <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $name }}</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
