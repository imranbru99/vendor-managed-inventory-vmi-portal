<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\ContactMessage;
use App\Models\DemoRequest;
use App\Models\Faq;
use App\Models\SubscriptionPlan;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function __construct(
        private readonly ModuleRegistry $modules,
    ) {}

    public function home(): View
    {
        $registryModules = $this->sortedModules();
        $faqs = Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('public.home', [
            'modules' => $registryModules,
            'faqs' => $faqs,
        ]);
    }

    public function about(): View
    {
        return $this->cmsOrView('about', 'public.about', [
            'title' => 'About VMI Portal',
            'lead' => 'We help enterprises align buyers and suppliers around shared inventory truth.',
        ]);
    }

    public function features(): View
    {
        return view('public.features', [
            'modules' => $this->sortedModules(),
        ]);
    }

    public function modules(): View
    {
        return view('public.modules.index', [
            'modules' => $this->sortedModules(),
        ]);
    }

    public function moduleShow(string $slug): View
    {
        $module = $this->modules->get($slug);

        abort_unless($module !== null, 404);

        return view('public.modules.show', [
            'slug' => $slug,
            'module' => $module,
            'dependencies' => $this->modules->dependencies($slug),
        ]);
    }

    public function vmi(): View
    {
        return $this->solution('vmi', 'Vendor Managed Inventory');
    }

    public function inventory(): View
    {
        return $this->solution('inventory', 'Inventory Intelligence');
    }

    public function suppliers(): View
    {
        return $this->solution('suppliers', 'Supplier Collaboration');
    }

    public function replenishment(): View
    {
        return $this->solution('replenishment', 'Automated Replenishment');
    }

    public function procurement(): View
    {
        return $this->solution('procurement', 'Procurement');
    }

    public function warehouse(): View
    {
        return $this->solution('warehouse', 'Warehouse Operations');
    }

    public function logistics(): View
    {
        return $this->solution('logistics', 'Logistics & Delivery');
    }

    public function consignment(): View
    {
        return $this->solution('consignment', 'Consignment Stock');
    }

    public function forecasting(): View
    {
        return $this->solution('forecasting', 'Demand Forecasting');
    }

    public function ai(): View
    {
        return $this->solution('ai', 'AI Assist');
    }

    public function integrations(): View
    {
        return $this->solution('integrations', 'Integrations');
    }

    public function industries(): View
    {
        return view('public.industries');
    }

    public function pricing(): View
    {
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($plans->isEmpty()) {
            $plans = collect(config('vmi.pricing.plans', []));
        }

        return view('public.pricing', ['plans' => $plans]);
    }

    public function security(): View
    {
        return view('public.security');
    }

    public function documentation(): View
    {
        return $this->cmsOrView('documentation', 'public.documentation', [
            'title' => 'Documentation',
            'lead' => 'Guides and references for deploying and operating VMI Portal.',
        ]);
    }

    public function blogIndex(): View
    {
        $posts = BlogPost::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('public.blog.index', compact('posts'));
    }

    public function blogShow(string $slug): View
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('public.blog.show', compact('post'));
    }

    public function faq(): View
    {
        $faqs = Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.faq', compact('faqs'));
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::query()->create([
            ...$validated,
            'status' => 'new',
        ]);

        return redirect()
            ->route('public.contact')
            ->with('success', 'Thank you — we will respond shortly.');
    }

    public function requestDemo(): View
    {
        return view('public.request-demo');
    }

    public function storeDemo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'industry' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        DemoRequest::query()->create([
            ...$validated,
            'status' => 'new',
        ]);

        return redirect()
            ->route('public.request-demo')
            ->with('success', 'Demo request received. Our team will reach out soon.');
    }

    public function demoLogin(): View
    {
        return view('public.demo-login', [
            'demo' => config('vmi.demo'),
        ]);
    }

    /**
     * @return Collection<int, array{slug: string, data: array}>
     */
    private function sortedModules(): Collection
    {
        return collect($this->modules->all())
            ->map(fn (array $data, string $slug) => ['slug' => $slug, 'data' => $data])
            ->sortBy(fn (array $row) => $row['data']['sort_order'])
            ->values();
    }

    private function solution(string $slug, string $headline): View
    {
        $module = $this->modules->get($slug);

        abort_unless($module !== null, 404);

        return view('public.solution', [
            'slug' => $slug,
            'headline' => $headline,
            'module' => $module,
        ]);
    }

    /**
     * @param  array<string, mixed>  $fallback
     */
    private function cmsOrView(string $cmsSlug, string $view, array $fallback = []): View
    {
        $page = CmsPage::query()
            ->where('slug', $cmsSlug)
            ->where('is_published', true)
            ->first();

        return view($view, array_merge($fallback, ['cms' => $page]));
    }
}
