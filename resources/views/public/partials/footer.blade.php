<footer class="mt-24 border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">
        <div>
            <p class="font-display text-lg font-semibold text-slate-900 dark:text-white"><span class="text-vmi">VMI</span> Portal</p>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Enterprise vendor managed inventory and supplier-controlled replenishment.</p>
            <a href="{{ route('public.demo') }}" class="mt-4 inline-block text-sm font-medium text-vmi hover:underline">Try the live demo →</a>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Platform</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <li><a href="{{ route('public.features') }}" class="hover:text-vmi">Features</a></li>
                <li><a href="{{ route('public.modules') }}" class="hover:text-vmi">Modules</a></li>
                <li><a href="{{ route('public.integrations') }}" class="hover:text-vmi">Integrations</a></li>
                <li><a href="{{ route('public.security') }}" class="hover:text-vmi">Security</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Company</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <li><a href="{{ route('public.about') }}" class="hover:text-vmi">About</a></li>
                <li><a href="{{ route('public.blog.index') }}" class="hover:text-vmi">Blog</a></li>
                <li><a href="{{ route('public.faq') }}" class="hover:text-vmi">FAQ</a></li>
                <li><a href="{{ route('public.contact') }}" class="hover:text-vmi">Contact</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Get started</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <li><a href="{{ route('public.pricing') }}" class="hover:text-vmi">Pricing</a></li>
                <li><a href="{{ route('public.request-demo') }}" class="hover:text-vmi">Request demo</a></li>
                <li><a href="{{ route('public.register') }}" class="hover:text-vmi">Register</a></li>
                <li><a href="{{ route('public.login') }}" class="hover:text-vmi">Login</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-100 px-4 py-6 text-center text-xs text-slate-500 dark:border-slate-800">
        © {{ date('Y') }} VMI Portal. Supplier-controlled replenishment for modern supply chains.
    </div>
</footer>
