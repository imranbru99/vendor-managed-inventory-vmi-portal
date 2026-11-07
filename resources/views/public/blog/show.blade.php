@extends('layouts.public')

@section('title', $post->title)
@section('meta_description', $post->seo_description ?? $post->excerpt)

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-16 lg:px-6">
        <p class="text-sm text-slate-500"><a href="{{ route('public.blog.index') }}" class="text-vmi hover:underline">Blog</a></p>
        <h1 class="font-display mt-4 text-4xl font-semibold text-slate-900 dark:text-white">{{ $post->title }}</h1>
        <time class="mt-4 block text-sm text-slate-500" datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->format('F j, Y') }}</time>
        <div class="prose-public mt-10 text-slate-700 dark:text-slate-300">
            {!! nl2br(e($post->body)) !!}
        </div>
    </article>
@endsection
