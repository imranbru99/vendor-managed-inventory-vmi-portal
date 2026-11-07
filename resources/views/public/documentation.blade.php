@extends('layouts.public')

@section('title', 'Documentation')

@section('content')
    @include('public.partials.page-hero', ['title' => $title, 'lead' => $lead])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6 prose-public text-slate-700 dark:text-slate-300">
        @if ($cms)
            {!! nl2br(e($cms->body)) !!}
        @else
            <p>Documentation covers tenant setup, module enablement, VMI policy configuration, replenishment runs, and integration patterns.</p>
            <h2>Quick links</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><a href="{{ route('public.modules') }}" class="text-vmi hover:underline">Module reference</a></li>
                <li><a href="{{ route('public.integrations') }}" class="text-vmi hover:underline">Integrations</a></li>
                <li><a href="{{ route('public.faq') }}" class="text-vmi hover:underline">FAQ</a></li>
            </ul>
        @endif
    </section>
@endsection
