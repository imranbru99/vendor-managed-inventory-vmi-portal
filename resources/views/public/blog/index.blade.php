@extends('layouts.public')

@section('title', 'Blog')

@section('content')
    @include('public.partials.page-hero', [
        'title' => 'Insights & updates',
        'lead' => 'VMI strategy, product news, and supply chain operations.',
    ])

    <section class="mx-auto max-w-7xl px-4 py-16 lg:px-6">
        @if ($posts->isEmpty())
            <p class="text-slate-600 dark:text-slate-400">No published posts yet. Check back soon.</p>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="module-card reveal-on-scroll rounded-xl p-6">
                        <time class="text-xs text-slate-500" datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('M j, Y') }}</time>
                        <h2 class="font-display mt-2 text-lg font-semibold">
                            <a href="{{ route('public.blog.show', $post->slug) }}" class="text-slate-900 hover:text-vmi dark:text-white">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $post->excerpt }}</p>
                    </article>
                @endforeach
            </div>
            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    </section>
@endsection
