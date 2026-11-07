@extends('layouts.public')

@section('title', 'Features')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Everything you need for modern VMI',
        'lead' => 'From agreements to automated replenishment — powered by a cohesive module set.',
    ])

    <section class="mx-auto max-w-7xl px-4 py-16 lg:px-6">
        <ul class="space-y-8">
            @foreach ($modules as $row)
                <li class="reveal-on-scroll flex flex-col gap-2 border-b border-slate-200 pb-8 last:border-0 dark:border-slate-800 sm:flex-row sm:justify-between">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">
                            <a href="{{ route('public.modules.show', $row['slug']) }}" class="hover:text-vmi">{{ $row['data']['name'] }}</a>
                        </h2>
                        <p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-400">{{ $row['data']['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endsection
