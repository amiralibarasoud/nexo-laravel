<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'درآمد ۳۰ روز اخیر (هزار تومان)';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = collect(range(29, 0))->map(fn($d) => [
            'day' => now()->subDays($d)->format('m/d'),
            'revenue' => (int) Order::where('status', 'paid')
                ->whereDate('created_at', now()->subDays($d))
                ->sum('amount') / 1000,
        ]);

        return [
            'datasets' => [
                [
                    'label' => 'درآمد',
                    'data' => $data->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'borderColor' => 'rgb(37, 99, 235)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('day')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
