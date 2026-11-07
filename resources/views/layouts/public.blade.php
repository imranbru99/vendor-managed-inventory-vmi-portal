<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="publicTheme()" :data-theme="theme" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VMI Portal') — Vendor Managed Inventory</title>
    <meta name="description" content="@yield('meta_description', 'Intelligent Vendor Managed Inventory — connect buyers and suppliers, automate replenishment, and improve supply chain visibility.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Source+Sans+3:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        vmi: { DEFAULT: '#0F766E', dark: '#0D5C56', light: '#14B8A6' },
                        accent: { cyan: '#22D3EE' },
                    },
                    fontFamily: {
                        display: ['Sora', 'system-ui', 'sans-serif'],
                        sans: ['Source Sans 3', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <link rel="stylesheet" href="{{ asset('css/public.css') }}">
    @stack('head')
</head>
<body class="public-site min-h-full antialiased" :class="theme === 'dark' ? 'theme-dark' : ''">
    @include('public.partials.nav')

    <main>
        @if (session('success'))
            <div class="max-w-3xl mx-auto px-4 pt-6" x-data x-init="setTimeout(() => $el.remove(), 6000)">
                <p class="rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-800 dark:text-teal-100">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        @yield('content')
    </main>

    @include('public.partials.footer')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function publicTheme() {
            return {
                theme: 'light',
                mobileOpen: false,
                init() {
                    const stored = localStorage.getItem('vmi-theme');
                    if (stored === 'dark' || stored === 'light') {
                        this.theme = stored;
                    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        this.theme = 'dark';
                    }
                    this.observeReveals();
                },
                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('vmi-theme', this.theme);
                },
                observeReveals() {
                    const run = () => {
                        const els = document.querySelectorAll('.reveal-on-scroll');
                        const io = new IntersectionObserver((entries) => {
                            entries.forEach((e) => {
                                if (e.isIntersecting) e.target.classList.add('is-visible');
                            });
                        }, { threshold: 0.12 });
                        els.forEach((el) => io.observe(el));
                    };
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', run);
                    } else {
                        run();
                    }
                },
            };
        }
    </script>
    @stack('scripts')
</body>
</html>
