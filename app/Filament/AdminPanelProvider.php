<?php

namespace App\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\InventoryStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('El Jardín de las Macetas')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/favicon/favicon-96x96.png'))
            ->colors([
                'primary' => Color::hex('#3a7f30'),
                'danger' => Color::hex('#d93a35'),
                'warning' => Color::hex('#f6b93b'),
                'success' => Color::hex('#4c9a3f'),
                'info' => Color::hex('#a85a2e'),
                'gray' => Color::Stone,
            ])
            ->font('Nunito')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('components.app-version'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    .fi-body{background:#fffaf0}
                    .dark .fi-body{background:#1c1712}
                    .fi-sidebar,.fi-topbar{border-color:#f6e4bf}
                    .fi-section,.fi-ta-ctn{border-radius:1.25rem}
                    .fi-btn{border-radius:.9rem}
                    .fi-sidebar-item-active>.fi-sidebar-item-btn{background:#dcefd5}
                    h1,.fi-header-heading{font-family:Fredoka,Nunito,sans-serif}
                </style>'
            )
            ->darkMode(true)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                InventoryStats::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
