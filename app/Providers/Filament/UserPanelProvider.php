<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetBackLocaleAndCurrency;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->authGuard('web')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->font(
                'Inter',
                provider: LocalFontProvider::class,
            )
            ->brandLogo(fn () => view('components.layouts.logo', ['imgClass' => 'w-16 h-16 mb-4', 'showText' => false]))
            ->favicon(asset('favicon-32x32.png'))
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
                Pages\Dashboard::class,
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
                SetBackLocaleAndCurrency::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->maxContentWidth('full')
            ->renderHook(
                'panels::topbar.start',
                fn () => '<a class="text-md font-bold" target="_blank" href="'.locaRoute('home').'">'.__('app.home').'</a>'
            )
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.render-hooks.topbar-end', ['panelId' => 'user'])
            );
    }
}
