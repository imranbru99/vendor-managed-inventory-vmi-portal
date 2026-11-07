@extends('layouts.public')

@section('title', 'FAQ')

@section('content')
    @include('public.partials.page-hero', ['title' => 'Frequently asked questions', 'lead' => 'Answers about VMI Portal, modules, and deployment.'])

    <section class="mx-auto max-w-3xl px-4 py-16 lg:px-6" x-data="{ open: null }">
        @forelse ($faqs as $faq)
            <div class="mb-3 rounded-xl border border-slate-200 dark:border-slate-700 reveal-on-scroll">
                <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}">
                    {{ $faq->question }}
                    <span x-text="open === {{ $faq->id }} ? '−' : '+'"></span>
                </button>
                <div x-show="open === {{ $faq->id }}" x-transition class="border-t px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400" style="display: none;">
                    {{ $faq->answer }}
                </div>
            </div>
        @empty
            <p class="text-slate-600 dark:text-slate-400">FAQs will appear here once published from the admin CMS.</p>
        @endforelse
    </section>
@endsection
