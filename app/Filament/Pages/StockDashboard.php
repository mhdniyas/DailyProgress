<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\StockStatsWidget;
use App\Filament\Widgets\StockComparisonWidget;
use App\Filament\Widgets\StockComparisonTable;

class StockDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.stock-dashboard';
    protected static ?string $title = 'Stock Dashboard';
    protected static ?string $navigationGroup = 'Stock Management';

    protected function getHeaderWidgets(): array
    {
        return [
            StockStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            StockComparisonWidget::class,
            StockComparisonTable::class,
        ];
    }
}
