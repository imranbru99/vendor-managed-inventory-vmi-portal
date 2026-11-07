@extends('layouts.public')

@section('title', 'Pricing')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Plans that scale with your network',
        'lead' => 'Start with a pilot site or deploy enterprise-wide VMI with advanced modules.',
    ])

    <section class="mx-auto max-w-7xl px-4 py-16 lg:px-6">
        <div class="grid gap-6 lg:grid-cols-4">
            @foreach ($plans as $plan)
                @php
                    $isModel = is_object($plan);
                    $name = $isModel ? $plan->name : $plan['name'];
                    $description = $isModel ? $plan->description : $plan['description'];
                    $monthly = $isModel ? $plan->price_monthly : $plan['price_monthly'];
                    $features = $isModel ? ($plan->features ?? []) : ($plan['features'] ?? []);
                    $highlight = $isModel ? ($plan->slug === 'professional') : ($plan['highlight'] ?? false);
                @endphp
                <div class="reveal-on-scroll module-card flex flex-col rounded-2xl p-6 {{ $highlight ? 'ring-2 ring-vmi' : '' }}">
                    <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">{{ $name }}</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $description }}</p>
                    <p class="mt-6 font-display text-3xl font-semibold text-slate-900 dark:text-white">
                        @if ($monthly)
                            ${{ number_format((float) $monthly, 0) }}<span class="text-base font-normal text-slate-500">/mo</span>
                        @else
                            Custom
                        @endif
                    </p>
                    <ul class="mt-6 flex-1 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        @foreach ($features as $feature)
                            <li class="flex gap-2"><span class="text-vmi">✓</span>{{ is_string($feature) ? $feature : ($feature['label'] ?? '') }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('public.request-demo') }}" class="mt-8 rounded-full {{ $highlight ? 'btn-primary text-white' : 'border border-slate-300 dark:border-slate-600' }} py-2.5 text-center text-sm font-semibold">Talk to sales</a>
                </div>
            @endforeach
        </div>
    </section>
@endsection
