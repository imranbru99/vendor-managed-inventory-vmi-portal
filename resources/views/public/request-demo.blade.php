@extends('layouts.public')

@section('title', 'Request Demo')

@section('content')
    @include('public.partials.page-hero', ['title' => 'Request a demo', 'lead' => 'Tell us about your VMI program and we will tailor a session.'])

    <section class="mx-auto max-w-xl px-4 py-16 lg:px-6">
        <form method="post" action="{{ route('public.request-demo.store') }}" class="space-y-5 reveal-on-scroll">
            @csrf
            <div>
                <label class="block text-sm font-medium" for="name">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-900">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="email">Work email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-900">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="company">Company</label>
                <input type="text" name="company" id="company" value="{{ old('company') }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-900">
                @error('company')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium" for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-medium" for="industry">Industry</label>
                    <input type="text" name="industry" id="industry" value="{{ old('industry') }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-900">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium" for="message">What would you like to see?</label>
                <textarea name="message" id="message" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-900">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="w-full rounded-full btn-primary py-3 text-sm font-semibold text-white">Submit request</button>
        </form>
    </section>
@endsection
