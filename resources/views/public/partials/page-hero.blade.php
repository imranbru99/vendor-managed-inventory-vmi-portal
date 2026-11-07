<section class="border-b border-slate-200 bg-gradient-to-br from-slate-50 to-teal-50/40 dark:border-slate-800 dark:from-slate-950 dark:to-teal-950/30">
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-6 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-widest text-vmi">{{ $eyebrow ?? 'VMI Portal' }}</p>
        <h1 class="font-display mt-3 max-w-3xl text-3xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ $title }}</h1>
        @if (! empty($lead))
            <p class="mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-400">{{ $lead }}</p>
        @endif
    </div>
</section>
