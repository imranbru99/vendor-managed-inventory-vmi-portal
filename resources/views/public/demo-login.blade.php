@extends('layouts.public')

@section('title', 'Demo')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Live demo environment',
        'lead' => 'Sign in with a demo persona to explore the admin portal.',
    ])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6 reveal-on-scroll">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm text-slate-600 dark:text-slate-400">Tenant: <span class="font-mono text-slate-900 dark:text-white">{{ $demo['tenant_slug'] }}</span></p>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-teal-500/30 bg-teal-50/50 p-5 dark:bg-teal-950/30">
                    <h2 class="font-display font-semibold text-slate-900 dark:text-white">Platform admin</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $demo['admin_email'] }}</p>
                    <p class="mt-1 font-mono text-xs text-slate-500">Password: {{ $demo['admin_password'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-700">
                    <h2 class="font-display font-semibold text-slate-900 dark:text-white">Buyer persona</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $demo['buyer_email'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Same password as admin in demo seed.</p>
                </div>
                <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-700 sm:col-span-2">
                    <h2 class="font-display font-semibold text-slate-900 dark:text-white">Supplier persona</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $demo['supplier_email'] }}</p>
                </div>
            </div>
            <a href="{{ url('/admin/login') }}" class="mt-8 inline-flex rounded-full btn-primary px-8 py-3 text-sm font-semibold text-white">Go to admin login</a>
        </div>
    </section>
@endsection
