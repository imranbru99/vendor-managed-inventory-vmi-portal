@extends('layouts.public')

@section('title', 'Home')

@section('content')
    <section class="hero-bleed hero-gradient relative min-h-[92vh] flex items-center text-white">
        <div class="pointer-events-none absolute -right-20 top-20 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl hero-orb"></div>
        <div class="pointer-events-none absolute bottom-10 left-10 h-96 w-96 rounded-full bg-teal-500/25 blur-3xl hero-orb" style="animation-delay: -4s;"></div>

        <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-20 lg:grid-cols-2 lg:items-center lg:px-6 lg:py-28">
            <div class="max-w-xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-200/90">VMI Portal</p>
                <h1 class="font-display mt-4 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-[2.75rem]">
                    Smarter Inventory. Stronger Supplier Collaboration. Automated Replenishment.
                </h1>
                <p class="mt-6 text-lg text-teal-100/90 leading-relaxed">
                    Take control of your supply chain with an intelligent Vendor Managed Inventory platform. Connect buyers and suppliers, monitor inventory, automate replenishment and improve supply chain visibility across every location.
                </p>
                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <a href="{{ route('public.request-demo') }}" class="relative inline-flex cta-pulse items-center rounded-full btn-primary px-7 py-3.5 text-sm font-semibold text-white shadow-xl shadow-black/25">
                        Request a demo
                    </a>
                    <a href="{{ route('public.demo') }}" class="inline-flex items-center rounded-full border border-white/30 bg-white/10 px-7 py-3.5 text-sm font-semibold backdrop-blur hover:bg-white/20">
                        Explore live demo
                    </a>
                </div>
            </div>

            <div class="relative hidden lg:block" aria-hidden="true">
                <svg viewBox="0 0 480 400" class="w-full max-w-lg drop-shadow-2xl" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="60" width="400" height="280" rx="16" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.2)"/>
                    <circle cx="120" cy="140" r="28" class="workflow-node"/>
                    <circle cx="240" cy="100" r="28" class="workflow-node"/>
                    <circle cx="360" cy="160" r="28" class="workflow-node"/>
                    <circle cx="200" cy="260" r="28" class="workflow-node"/>
                    <circle cx="320" cy="280" r="28" class="workflow-node"/>
                    <path d="M148 140 H212" stroke="#22D3EE" stroke-width="2" class="workflow-line"/>
                    <path d="M268 108 H332" stroke="#22D3EE" stroke-width="2" class="workflow-line" style="animation-delay: 0.4s"/>
                    <path d="M128 168 L188 244" stroke="#5EEAD4" stroke-width="2" class="workflow-line" style="animation-delay: 0.8s"/>
                    <path d="M248 124 L228 232" stroke="#5EEAD4" stroke-width="2" class="workflow-line"/>
                    <path d="M228 280 H292" stroke="#22D3EE" stroke-width="2" class="workflow-line"/>
                    <text x="120" y="145" text-anchor="middle" fill="white" font-size="11" font-family="Sora">Buyer</text>
                    <text x="240" y="105" text-anchor="middle" fill="white" font-size="11" font-family="Sora">Stock</text>
                    <text x="360" y="165" text-anchor="middle" fill="white" font-size="11" font-family="Sora">Supplier</text>
                    <text x="200" y="265" text-anchor="middle" fill="white" font-size="10" font-family="Sora">VMI</text>
                    <text x="320" y="285" text-anchor="middle" fill="white" font-size="10" font-family="Sora">Replenish</text>
                </svg>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 lg:px-6 reveal-on-scroll">
        <div class="max-w-2xl">
            <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">One platform for inventory truth</h2>
            <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">Replace spreadsheet VMI with governed agreements, live balances, and replenishment that respects your policies — across buyers, suppliers, and every warehouse.</p>
        </div>
        <ul class="mt-12 grid gap-8 sm:grid-cols-3">
            <li>
                <p class="font-display text-lg font-semibold text-vmi">Shared visibility</p>
                <p class="mt-2 text-slate-600 dark:text-slate-400">Suppliers see authorized inventory and demand signals without exposing your entire ERP.</p>
            </li>
            <li>
                <p class="font-display text-lg font-semibold text-vmi">Policy-driven VMI</p>
                <p class="mt-2 text-slate-600 dark:text-slate-400">Min/max, safety stock, and approval workflows embedded in every replenishment cycle.</p>
            </li>
            <li>
                <p class="font-display text-lg font-semibold text-vmi">Faster fulfilment</p>
                <p class="mt-2 text-slate-600 dark:text-slate-400">From proposal to PO to shipment — fewer stockouts and less expediting.</p>
            </li>
        </ul>
    </section>

    <section class="bg-slate-900 py-20 text-white reveal-on-scroll">
        <div class="mx-auto max-w-7xl px-4 lg:px-6">
            <h2 class="font-display text-3xl font-semibold">How VMI works in VMI Portal</h2>
            <p class="mt-3 max-w-2xl text-slate-400">A continuous loop from agreement to replenishment — illustrated below.</p>
            <div class="mt-12 overflow-x-auto">
                <ol class="flex min-w-[640px] items-center justify-between gap-4 text-sm">
                    @foreach (['Agree terms', 'Monitor stock', 'Detect need', 'Propose order', 'Approve & ship'] as $step)
                        <li class="flex flex-1 flex-col items-center text-center">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-cyan-400/60 bg-teal-900/50 font-display text-xs font-semibold">{{ $loop->iteration }}</span>
                            <span class="mt-3 text-slate-300">{{ $step }}</span>
                        </li>
                        @if (! $loop->last)
                            <li class="h-px flex-1 bg-gradient-to-r from-teal-500/50 to-cyan-400/50" aria-hidden="true"></li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 lg:px-6 reveal-on-scroll">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">Supplier collaboration</h2>
                <p class="mt-4 text-slate-600 dark:text-slate-400">Onboard trading partners, share scoped inventory views, and align on replenishment cadence. Buyers stay in control; suppliers gain the signal they need to plan production and delivery.</p>
                <a href="{{ route('public.suppliers') }}" class="mt-6 inline-flex text-sm font-semibold text-vmi hover:underline">Supplier network module →</a>
            </div>
            <div class="rounded-2xl border border-teal-500/20 bg-gradient-to-br from-teal-50 to-white p-8 dark:from-teal-950/40 dark:to-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-vmi">Collaboration hub</p>
                <p class="mt-4 font-display text-xl text-slate-800 dark:text-slate-100">Invitations, relationships, and acknowledgments in one audit trail.</p>
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/50 reveal-on-scroll">
        <div class="mx-auto max-w-7xl px-4 lg:px-6">
            <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">Inventory intelligence</h2>
            <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">Balances by location, stale-stock alerts, and movement history — tied to catalog and warehouse master data.</p>
            <a href="{{ route('public.inventory') }}" class="mt-6 inline-flex text-sm font-semibold text-vmi">Explore inventory →</a>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 lg:px-6 reveal-on-scroll">
        <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">Replenishment automation</h2>
        <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">Runs evaluate demand, policies, and constraints — then route proposals through your approval workflow before orders hit procurement.</p>
        <a href="{{ route('public.replenishment') }}" class="mt-6 inline-flex rounded-full border border-vmi px-5 py-2 text-sm font-semibold text-vmi hover:bg-teal-50 dark:hover:bg-teal-950/30">Replenishment module</a>
    </section>

    <section class="bg-slate-100 py-20 dark:bg-slate-950 reveal-on-scroll">
        <div class="mx-auto max-w-7xl px-4 lg:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">Modular platform</h2>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">Enable only what you need — descriptions from the live module registry.</p>
                </div>
                <a href="{{ route('public.modules') }}" class="text-sm font-semibold text-vmi">View all modules</a>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($modules->take(9) as $row)
                    <a href="{{ route('public.modules.show', $row['slug']) }}" class="module-card rounded-xl p-5">
                        <p class="font-display font-semibold text-slate-900 dark:text-white">{{ $row['data']['name'] }}</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $row['data']['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 lg:px-6 reveal-on-scroll">
        <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">Built for your industry</h2>
        <p class="mt-3 text-slate-600 dark:text-slate-400">Manufacturing, distribution, retail, healthcare, and industrial MRO — same VMI core, configured per tenant.</p>
        <a href="{{ route('public.industries') }}" class="mt-6 inline-flex text-sm font-semibold text-vmi">Industry solutions →</a>
    </section>

    <section class="border-t border-slate-200 py-20 dark:border-slate-800 reveal-on-scroll">
        <div class="mx-auto max-w-7xl px-4 lg:px-6">
            <h2 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">Integrations</h2>
            <p class="mt-3 max-w-xl text-slate-600 dark:text-slate-400">Connect ERP, EDI, and webhooks — inventory and orders stay synchronized with systems of record.</p>
            <a href="{{ route('public.integrations') }}" class="mt-6 inline-flex text-sm font-semibold text-vmi">Integration hub →</a>
        </div>
    </section>

    <section class="bg-teal-950 py-16 text-white reveal-on-scroll">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-6 px-4 lg:px-6">
            <div>
                <h2 class="font-display text-2xl font-semibold">Security by design</h2>
                <p class="mt-2 max-w-lg text-teal-100/80">Tenant isolation, role-based access, and audit logging for regulated supply chains.</p>
            </div>
            <a href="{{ route('public.security') }}" class="rounded-full border border-white/30 px-6 py-2.5 text-sm font-semibold hover:bg-white/10">Security overview</a>
        </div>
    </section>

    @if ($faqs->isNotEmpty())
        <section class="mx-auto max-w-3xl px-4 py-20 lg:px-6 reveal-on-scroll" x-data="{ open: null }">
            <h2 class="font-display text-center text-3xl font-semibold text-slate-900 dark:text-white">Questions</h2>
            <div class="mt-10 space-y-3">
                @foreach ($faqs as $faq)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700">
                        <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}">
                            {{ $faq->question }}
                            <span x-text="open === {{ $faq->id }} ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === {{ $faq->id }}" x-transition class="border-t border-slate-100 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400" style="display: none;">
                            {{ $faq->answer }}
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 text-center"><a href="{{ route('public.faq') }}" class="text-sm font-semibold text-vmi">Full FAQ →</a></p>
        </section>
    @endif

    <section class="hero-bleed relative overflow-hidden bg-gradient-to-r from-vmi to-teal-600 py-20 text-center text-white">
        <div class="relative mx-auto max-w-2xl px-4">
            <h2 class="font-display text-3xl font-semibold">Ready to modernize VMI?</h2>
            <p class="mt-4 text-teal-50">Schedule a tailored walkthrough with your workflows and data model.</p>
            <a href="{{ route('public.request-demo') }}" class="mt-8 inline-flex rounded-full bg-white px-8 py-3 text-sm font-semibold text-vmi shadow-lg hover:bg-slate-50">Book your demo</a>
        </div>
    </section>
@endsection
