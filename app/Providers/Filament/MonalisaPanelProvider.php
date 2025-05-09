<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MonalisaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('monalisa')
            ->path('systems/monalisa')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Monalisa/Resources'), for: 'App\\Filament\\Monalisa\\Resources')
            ->discoverPages(in: app_path('Filament/Monalisa/Pages'), for: 'App\\Filament\\Monalisa\\Pages')
            ->pages([
                \App\Filament\Monalisa\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Monalisa/Widgets'), for: 'App\\Filament\\Monalisa\\Widgets')
            ->brandName('MONALISA')
            ->brandLogo(asset('img/logo.png'))
            ->favicon(asset('img/favicon.ico'))
            ->navigationGroups([
                'Domain & Indikator',
                'Dokumen',
                'Timeline',
                'Pengaturan',
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
