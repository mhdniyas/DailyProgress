<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\StockRecord;
use App\Models\ShopStockRecord;

class StockStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $latestDate = StockRecord::max('recorded_at');
        
        $systemStock = StockRecord::whereDate('recorded_at', $latestDate)
            ->sum('total_quantity');
        $shopStock = ShopStockRecord::whereDate('recorded_at', $latestDate)
            ->sum('total_quantity');
        $difference = $systemStock - $shopStock;

        return [
            Stat::make('Total System Stock', number_format($systemStock))
                ->description('Current system inventory')
                ->descriptionIcon('heroicon-m-cube') // Updated icon
                ->color('success'),

            Stat::make('Total Shop Stock', number_format($shopStock))
                ->description('Current shop inventory')
                ->descriptionIcon('heroicon-m-shopping-bag') // Updated icon
                ->color('primary'),

            Stat::make('Stock Difference', number_format(abs($difference)))
                ->description($difference >= 0 ? 'Excess in System' : 'Shortage in System')
                ->descriptionIcon($difference >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down') // Updated icons
                ->color($difference >= 0 ? 'success' : 'danger'),
        ];
    }
}
