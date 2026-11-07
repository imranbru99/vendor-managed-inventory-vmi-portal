@extends('layouts.public')

@section('title', 'Modules')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Platform modules',
        'lead' => 'Every capability below is registered in ModuleRegistry — your single source of truth for names and descriptions.',
    ])

    <section class="mx-auto max-w-7xl px-4 py-16 lg:px-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $row)
                <a href="{{ route('public.modules.show', $row['slug']) }}" class="module-card reveal-on-scroll rounded-xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-vmi">{{ ucfirst($row['data']['category']->value) }}</p>
                    <p class="font-display mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $row['data']['name'] }}</p>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $row['data']['description'] }}</p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
