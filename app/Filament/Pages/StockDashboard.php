<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\StockStatsWidget;
use App\Filament\Widgets\StockComparisonWidget;

class StockDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.stock-dashboard';
    protected static ?string $title = 'Stock Dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            StockStatsWidget::class,
            StockComparisonWidget::class,
        ];
    }
}
