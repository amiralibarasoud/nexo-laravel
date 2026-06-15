<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', 'paid')->sum('amount');
        $thisMonthRevenue = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        $lastMonthRevenue = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        $revenueChange = $lastMonthRevenue > 0
            ? round(($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100)
            : 0;

        $todayEnrollments = Enrollment::whereDate('enrolled_at', today())->count();
        $weekEnrollments = Enrollment::where('enrolled_at', '>=', now()->subDays(7))->count();

        // Revenue chart (last 7 days)
        $revenueChart = collect(range(6, 0))->map(fn($d) =>
            (int) Order::where('status', 'paid')
                ->whereDate('created_at', now()->subDays($d))
                ->sum('amount') / 1000
        )->toArray();

        $enrollChart = collect(range(6, 0))->map(fn($d) =>
            Enrollment::whereDate('enrolled_at', now()->subDays($d))->count()
        )->toArray();

        return [
            Stat::make('کل درآمد', price($totalRevenue))
                ->description("این ماه: " . price($thisMonthRevenue))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($revenueChart),

            Stat::make('کل کاربران', toFarsiNumber(User::count()))
                ->description('کاربران فعال: ' . toFarsiNumber(User::where('is_active', true)->count()))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('ثبت‌نام‌ها', toFarsiNumber(Enrollment::count()))
                ->description('امروز: ' . toFarsiNumber($todayEnrollments) . ' | این هفته: ' . toFarsiNumber($weekEnrollments))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->chart($enrollChart),

            Stat::make('دوره‌های منتشر شده', toFarsiNumber(Course::published()->count()))
                ->description('کل دوره‌ها: ' . toFarsiNumber(Course::count()))
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),

            Stat::make('سفارشات امروز', toFarsiNumber(Order::whereDate('created_at', today())->where('status', 'paid')->count()))
                ->description('درآمد امروز: ' . price(Order::whereDate('created_at', today())->where('status', 'paid')->sum('amount')))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
        ];
    }
}
