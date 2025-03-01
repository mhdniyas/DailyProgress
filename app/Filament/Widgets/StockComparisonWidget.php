<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\StockRecord;
use App\Models\ShopStockRecord;
use Carbon\Carbon;

class StockComparisonWidget extends LineChartWidget
{
    protected static ?string $heading = 'Stock Comparison';

    protected function getData(): array
    {
        $days = 7;
        $labels = [];
        $systemData = [];
        $shopData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');

            $systemStock = StockRecord::whereDate('recorded_at', $date)
                ->sum('total_quantity');
            $shopStock = ShopStockRecord::whereDate('recorded_at', $date)
                ->sum('total_quantity');

            $systemData[] = $systemStock;
            $shopData[] = $shopStock;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'System Stock',
                    'data' => $systemData,
                    'borderColor' => '#9061F9',
                ],
                [
                    'label' => 'Shop Stock',
                    'data' => $shopData,
                    'borderColor' => '#E74694',
                ],
            ],
        ];
    }
}
