<header class="sticky top-0 z-50 nav-glass" x-data="{ solutionsOpen: false }">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 lg:px-6">
        <a href="{{ route('public.home') }}" class="font-display text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
            <span class="text-vmi">VMI</span> Portal
        </a>

        <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-300 lg:flex">
            <div class="relative" @mouseenter="solutionsOpen = true" @mouseleave="solutionsOpen = false">
                <button type="button" class="inline-flex items-center gap-1 hover:text-vmi" @click="solutionsOpen = !solutionsOpen">
                    Solutions
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="solutionsOpen" x-transition.opacity class="absolute left-0 top-full z-50 mt-2 w-56 rounded-xl border border-slate-200/80 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900" style="display: none;">
                    <a href="{{ route('public.vmi') }}" class="block rounded-lg px-3 py-2 hover:bg-teal-50 dark:hover:bg-teal-950/40">VMI</a>
                    <a href="{{ route('public.inventory') }}" class="block rounded-lg px-3 py-2 hover:bg-teal-50 dark:hover:bg-teal-950/40">Inventory</a>
                    <a href="{{ route('public.replenishment') }}" class="block rounded-lg px-3 py-2 hover:bg-teal-50 dark:hover:bg-teal-950/40">Replenishment</a>
                    <a href="{{ route('public.suppliers') }}" class="block rounded-lg px-3 py-2 hover:bg-teal-50 dark:hover:bg-teal-950/40">Suppliers</a>
                    <a href="{{ route('public.modules') }}" class="mt-1 block rounded-lg border-t border-slate-100 px-3 py-2 text-vmi dark:border-slate-800">All modules →</a>
                </div>
            </div>
            <a href="{{ route('public.features') }}" class="hover:text-vmi">Features</a>
            <a href="{{ route('public.pricing') }}" class="hover:text-vmi">Pricing</a>
            <a href="{{ route('public.industries') }}" class="hover:text-vmi">Industries</a>
            <a href="{{ route('public.blog.index') }}" class="hover:text-vmi">Blog</a>
            <a href="{{ route('public.documentation') }}" class="hover:text-vmi">Docs</a>
        </nav>

        <div class="flex items-center gap-2">
            <div class="hidden items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-xs text-slate-500 dark:border-slate-700 sm:flex" title="Language switcher (coming soon)">
                <span class="font-medium text-vmi">EN</span>
                <span aria-hidden="true">|</span>
                <span class="opacity-50">ES</span>
            </div>
            <button type="button" @click="toggleTheme()" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Toggle theme">
                <span x-show="theme === 'light'">🌙</span>
                <span x-show="theme === 'dark'" style="display:none">☀️</span>
            </button>
            <a href="{{ route('public.login') }}" class="hidden text-sm font-medium text-slate-600 hover:text-vmi dark:text-slate-300 sm:inline">Log in</a>
            <a href="{{ route('public.request-demo') }}" class="hidden rounded-full btn-primary px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-teal-900/20 sm:inline">Request demo</a>
            <button type="button" class="rounded-lg p-2 lg:hidden" @click="mobileOpen = !mobileOpen" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" x-transition class="border-t border-slate-200 bg-white px-4 py-4 dark:border-slate-800 dark:bg-slate-950 lg:hidden" style="display: none;">
        <div class="flex flex-col gap-2 text-sm font-medium">
            <a href="{{ route('public.features') }}" class="py-2">Features</a>
            <a href="{{ route('public.modules') }}" class="py-2">Modules</a>
            <a href="{{ route('public.pricing') }}" class="py-2">Pricing</a>
            <a href="{{ route('public.contact') }}" class="py-2">Contact</a>
            <a href="{{ route('public.login') }}" class="py-2">Log in</a>
            <a href="{{ route('public.request-demo') }}" class="rounded-full btn-primary py-2 text-center text-white">Request demo</a>
        </div>
    </div>
</header>
