<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Support\HtmlString;
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
            ->login()
            ->brandName('نکسو کورس')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray'    => Color::Slate,
            ])
            ->font('Vazirmatn', url: 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): HtmlString => new HtmlString('
                <style>
                    /* فونت فارسی برای کل پنل */
                    *, *::before, *::after {
                        font-family: Vazirmatn, sans-serif !important;
                    }
                    /* راست‌چینی متون و عناوین */
                    .fi-header-heading,
                    .fi-section-header-heading,
                    .fi-ta-header-cell,
                    .fi-ta-cell,
                    .fi-fo-field-wrp label,
                    .fi-in-text,
                    .fi-wi-stats-overview-stat-label,
                    .fi-wi-stats-overview-stat-value,
                    .fi-wi-stats-overview-stat-description,
                    .fi-breadcrumbs,
                    p, span, div, td, th, label, h1, h2, h3, h4 {
                        text-align: right;
                    }
                    /* ستون‌های جدول راست‌چین */
                    table { direction: rtl; }
                    /* فرم‌ها */
                    .fi-fo-field-wrp { direction: rtl; }
                    /* breadcrumb */
                    .fi-breadcrumbs ol { flex-direction: row-reverse; }
                    /* header actions */
                    .fi-header-actions { flex-direction: row-reverse; }
                </style>')
            )
            ->navigationGroups([
                NavigationGroup::make('مدیریت دوره‌ها')->collapsible(false),
                NavigationGroup::make('بلاگ'),
                NavigationGroup::make('مدیریت مالی'),
                NavigationGroup::make('مدیریت کاربران'),
                NavigationGroup::make('تنظیمات'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\RevenueChart::class,
                Widgets\AccountWidget::class,
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
