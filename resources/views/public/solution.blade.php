@extends('layouts.public')

@section('title', $module['name'])
@section('meta_description', $module['description'])

@section('content')
    @include('public.partials.page-hero', [
        'title' => $headline,
        'lead' => $module['description'],
        'eyebrow' => $module['name'],
    ])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6 reveal-on-scroll">
        <p class="text-lg text-slate-600 dark:text-slate-400">
            {{ $module['description'] }} Activate this module in your tenant alongside related capabilities in the VMI Portal modular architecture.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('public.modules.show', $slug) }}" class="rounded-full btn-primary px-6 py-2.5 text-sm font-semibold text-white">Module details</a>
            <a href="{{ route('public.request-demo') }}" class="rounded-full border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200">Request demo</a>
        </div>
    </section>
@endsection
