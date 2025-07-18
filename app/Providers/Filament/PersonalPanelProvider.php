<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Services\LocaleCurrencyService;
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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PersonalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $service = new LocaleCurrencyService();

        // ------------------------------
        // 设置语言
        // ------------------------------
        $locale = request()->segment(1); // 取 URI 第一段

        $supported = $service->getLanguages()->pluck('code')->toArray();

        if (!in_array($locale, $supported)) {
            $locale = $service->getDefaultLanguageCode();
        }

        return $panel
            ->default()
            ->id('personal')
            ->path($locale . '/personal')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Green,
            ])
            ->font(
                'Inter',
                provider: LocalFontProvider::class,
            )
            ->brandLogo(fn() => view('components.layouts.logo', ['imgClass' => 'w-11 h-11']))
            ->favicon(asset('favicon.png'))
            ->discoverResources(in: app_path('Filament/Personal/Resources'), for: 'App\\Filament\\Personal\\Resources')
            ->discoverPages(in: app_path('Filament/Personal/Pages'), for: 'App\\Filament\\Personal\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Personal/Widgets'), for: 'App\\Filament\\Personal\\Widgets')
            ->widgets([
                \App\Filament\Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
                SetLocaleAndCurrency::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
