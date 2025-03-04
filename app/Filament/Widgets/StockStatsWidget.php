<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\StockRecord;
use App\Models\ShopStockRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StockStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $userId = auth()->id();

        // Get all items for the current user
        $items = Item::where('user_id', $userId)->get();

        // Calculate total rice stats
        $totalRiceSystem = 0;
        $totalRiceShop = 0;

        foreach ($items as $item) {
            // Get latest system stock for the user
            $systemStock = StockRecord::where('user_id', $userId)
                ->where('item_id', $item->id)
                ->orderBy('recorded_at', 'desc')
                ->first();

            // Get latest shop stock for the user
            $shopStock = ShopStockRecord::where('user_id', $userId)
                ->where('item_id', $item->id)
                ->orderBy('recorded_at', 'desc')
                ->first();

            // Calculate difference
            $difference = 0;
            if ($systemStock && $shopStock) {
                $difference = $systemStock->total_quantity - $shopStock->total_quantity;
            }

            // Add to total rice if the item name contains 'rice' (case insensitive)
            if (stripos($item->name, 'rice') !== false) {
                $totalRiceSystem += $systemStock ? $systemStock->total_quantity : 0;
                $totalRiceShop += $shopStock ? $shopStock->total_quantity : 0;
            }

            $stats[] = Stat::make($item->name, number_format($difference, 2))
                ->description('System: ' . ($systemStock ? number_format($systemStock->total_quantity, 2) : '0') .
                            ' | Shop: ' . ($shopStock ? number_format($shopStock->total_quantity, 2) : '0'))
                ->color($difference < 0 ? 'danger' : 'success');
        }

        // Add total rice stats at the beginning of the array
        array_unshift($stats,
            Stat::make('Total Rice (System)', number_format($totalRiceSystem, 2))
                ->description('Approx. ' . number_format($totalRiceSystem / 50, 1) . ' bags (50kg)')
                ->color('info'),

            Stat::make('Total Rice (Shop)', number_format($totalRiceShop, 2))
                ->description('Approx. ' . number_format($totalRiceShop / 50, 1) . ' bags (50kg)')
                ->color('info'),

            Stat::make('Rice Difference', number_format($totalRiceSystem - $totalRiceShop, 2))
                ->description('Approx. ' . number_format(($totalRiceSystem - $totalRiceShop) / 50, 1) . ' bags (50kg)')
                ->color(($totalRiceSystem - $totalRiceShop) >= 0 ? 'success' : 'danger')
        );

        return $stats;
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
