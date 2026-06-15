<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', 'paid')->sum('amount');
        $todayRevenue = Order::where('status', 'paid')
            ->whereDate('created_at', today())
            ->sum('amount');

        return [
            Stat::make('کل درآمد', price($totalRevenue))
                ->description('درآمد کل پلتفرم')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('درآمد امروز', price($todayRevenue))
                ->description('فروش امروز')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('کاربران', toFarsiNumber(User::count()))
                ->description('کل کاربران ثبت‌نام شده')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('دانش‌آموزان', toFarsiNumber(Enrollment::count()))
                ->description('کل ثبت‌نام دوره‌ها')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('دوره‌ها', toFarsiNumber(Course::published()->count()))
                ->description('دوره‌های منتشر شده')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('gray'),
        ];
    }
}
