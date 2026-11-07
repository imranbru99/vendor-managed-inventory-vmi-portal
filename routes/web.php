<?php

use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');

Route::controller(PublicSiteController::class)->group(function (): void {
    Route::get('/about', 'about')->name('public.about');
    Route::get('/features', 'features')->name('public.features');
    Route::get('/modules', 'modules')->name('public.modules');
    Route::get('/modules/{slug}', 'moduleShow')->name('public.modules.show');

    Route::get('/vmi', 'vmi')->name('public.vmi');
    Route::get('/inventory', 'inventory')->name('public.inventory');
    Route::get('/suppliers', 'suppliers')->name('public.suppliers');
    Route::get('/replenishment', 'replenishment')->name('public.replenishment');
    Route::get('/procurement', 'procurement')->name('public.procurement');
    Route::get('/warehouse', 'warehouse')->name('public.warehouse');
    Route::get('/logistics', 'logistics')->name('public.logistics');
    Route::get('/consignment', 'consignment')->name('public.consignment');
    Route::get('/forecasting', 'forecasting')->name('public.forecasting');
    Route::get('/ai', 'ai')->name('public.ai');
    Route::get('/integrations', 'integrations')->name('public.integrations');

    Route::get('/industries', 'industries')->name('public.industries');
    Route::get('/pricing', 'pricing')->name('public.pricing');
    Route::get('/security', 'security')->name('public.security');
    Route::get('/documentation', 'documentation')->name('public.documentation');

    Route::get('/blog', 'blogIndex')->name('public.blog.index');
    Route::get('/blog/{slug}', 'blogShow')->name('public.blog.show');

    Route::get('/faq', 'faq')->name('public.faq');
    Route::get('/contact', 'contact')->name('public.contact');
    Route::post('/contact', 'storeContact')->name('public.contact.store');
    Route::get('/request-demo', 'requestDemo')->name('public.request-demo');
    Route::post('/request-demo', 'storeDemo')->name('public.request-demo.store');

    Route::get('/demo', 'demoLogin')->name('public.demo');
});

Route::redirect('/login', '/admin/login')->name('login');
Route::get('/sign-in', fn () => redirect()->route('login'))->name('public.login');
Route::redirect('/register', '/request-demo')->name('register');
Route::get('/sign-up', fn () => redirect()->route('register'))->name('public.register');
