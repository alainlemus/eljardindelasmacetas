<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\PublicPage;
use App\Support\AppVersion;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// El catálogo público no usa sesión ni cookies: así no se crea una sesión por visitante/bot
// y la respuesta es cacheable.
Route::middleware(PublicPage::class)
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class, EncryptCookies::class, AddQueuedCookiesToResponse::class])
    ->group(function () {
        Route::get('/', [CatalogController::class, 'index'])->name('home');
        Route::get('/catalog/share', [CatalogController::class, 'share'])->name('catalog.share');
        Route::get('/catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.product');
        Route::get('/og/{slug}.jpg', [SeoController::class, 'ogImage'])->where('slug', '[a-z0-9-]+')->name('og.figure');
    });

// Una sola URL para el listado: /catalog (y sus parámetros) redirige a /.
Route::get('/catalog', fn () => redirect()->route('home', request()->query(), 301))->name('catalog');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/version.json', fn () => response()->json(AppVersion::toArray()))->name('version');
