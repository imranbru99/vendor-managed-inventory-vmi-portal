@extends('layouts.public')

@section('title', 'Security')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Enterprise security',
        'lead' => 'Multi-tenant isolation, least-privilege access, and comprehensive audit trails.',
    ])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6 space-y-8 text-slate-700 dark:text-slate-300 reveal-on-scroll">
        <div>
            <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">Tenant isolation</h2>
            <p class="mt-2">Every organization operates in a dedicated tenant context with scoped data and module entitlements.</p>
        </div>
        <div>
            <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">Access control</h2>
            <p class="mt-2">Role-based permissions align buyers, suppliers, and administrators to the actions they need — nothing more.</p>
        </div>
        <div>
            <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">Audit & compliance</h2>
            <p class="mt-2">Inventory movements, approvals, and configuration changes are logged for operational and regulatory review.</p>
        </div>
    </section>
@endsection
