@extends('layouts.public')

@section('title', $module['name'])
@section('meta_description', $module['description'])

@section('content')
    @include('public.partials.page-hero', [
        'title' => $module['name'],
        'lead' => $module['description'],
        'eyebrow' => 'Module',
    ])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6 reveal-on-scroll">
        <dl class="grid gap-6 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Slug</dt>
                <dd class="mt-1 font-mono text-sm text-slate-800 dark:text-slate-200">{{ $slug }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Category</dt>
                <dd class="mt-1 text-sm">{{ ucfirst($module['category']->value) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Required</dt>
                <dd class="mt-1 text-sm">{{ $module['is_required'] ? 'Yes' : 'Optional' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase text-slate-500">Dependencies</dt>
                <dd class="mt-2 flex flex-wrap gap-2">
                    @forelse ($dependencies as $dep)
                        <a href="{{ route('public.modules.show', $dep) }}" class="rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-vmi dark:bg-teal-950/50">{{ $dep }}</a>
                    @empty
                        <span class="text-sm text-slate-500">None</span>
                    @endforelse
                </dd>
            </div>
        </dl>
        <a href="{{ route('public.modules') }}" class="mt-10 inline-flex text-sm font-semibold text-vmi">← All modules</a>
    </section>
@endsection
