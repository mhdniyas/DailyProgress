<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CreditStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Credits Today',
                Transaction::where('type', 'credit')
                    ->whereDate('date', Carbon::today())
                    ->sum('amount'))
                ->description('Total money in today')
                ->color('success')
                ->chart([/* Add chart data here */]),

            Stat::make('Total Credits This Month',
                Transaction::where('type', 'credit')
                    ->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year)
                    ->sum('amount'))
                ->description('Total money in this month')
                ->color('success')
                ->chart([/* Add chart data here */]),

            Stat::make('Total Credits This Year',
                Transaction::where('type', 'credit')
                    ->whereYear('date', Carbon::now()->year)
                    ->sum('amount'))
                ->description('Total money in this year')
                ->color('success')
                ->chart([/* Add chart data here */]),
        ];
    }
}
