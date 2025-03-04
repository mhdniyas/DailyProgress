<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\StockStatsWidget;
use App\Filament\Widgets\StockComparisonWidget;
use App\Filament\Widgets\StockComparisonTable;
use Illuminate\Database\Eloquent\Builder;

class StockDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.stock-dashboard';
    protected static ?string $title = 'Stock Dashboard';
    protected static ?string $navigationGroup = 'Stock Management';

    public function mount(): void
    {
        // Ensure user can only access their own dashboard
        abort_unless(auth()->check(), 403);
    }

    public function getHeading(): string
    {
        return 'Stock Report - ' . auth()->user()->name;
    }

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

    // Changed from protected to public
    public function getWidgetData(): array
    {
        return [
            'user_id' => auth()->id(),
        ];
    }
}
