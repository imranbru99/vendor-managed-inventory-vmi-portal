@extends('layouts.public')

@section('title', 'About')

@section('content')
    @include('public.partials.page-hero', ['title' => $title, 'lead' => $lead])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6 prose-public text-slate-700 dark:text-slate-300">
        @if ($cms)
            {!! nl2br(e($cms->body)) !!}
        @else
            <p>VMI Portal is an enterprise SaaS platform for vendor managed inventory and supplier-controlled replenishment. We connect buyers and suppliers on shared data, automate policy-based replenishment, and give operations teams visibility without ripping out their ERP.</p>
            <p class="mt-4">Our team builds modular supply-chain software — multi-tenant, integration-ready, and designed for auditors and operators alike.</p>
        @endif
    </section>
@endsection
