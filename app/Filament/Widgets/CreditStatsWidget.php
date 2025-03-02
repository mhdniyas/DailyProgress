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
        $todayCredit = Transaction::where('type', 'credit')
            ->whereDate('date', Carbon::today())
            ->sum('amount');

        $todayDebit = Transaction::where('type', 'debit')
            ->whereDate('date', Carbon::today())
            ->sum('amount');

        $todayTotal = $todayCredit - $todayDebit;

        return [
            // Today's Cashflow Stats
            Stat::make("Today's Credit", $todayCredit)
                ->description('Total income today')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make("Today's Debit", $todayDebit)
                ->description('Total expenses today')
                ->color('danger')
                ->chart([3, 5, 2, 7, 4, 3, 6]),

            Stat::make("Today's Balance", $todayTotal)
                ->description('Net cashflow today')
                ->color($todayTotal >= 0 ? 'success' : 'danger')
                ->chart([4, 5, 3, 6, 3, 5, 4]),

            // Existing Monthly and Yearly Stats
            Stat::make(
                'Total Credits This Month',
                Transaction::where('type', 'credit')
                    ->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year)
                    ->sum('amount')
            )
                ->description('Total money in this month')
                ->color('success')
                ->chart([3, 5, 7, 4, 5, 3, 6]),

            Stat::make(
                'Total Credits This Year',
                Transaction::where('type', 'credit')
                    ->whereYear('date', Carbon::now()->year)
                    ->sum('amount')
            )
                ->description('Total money in this year')
                ->color('success')
                ->chart([5, 4, 7, 3, 5, 4, 6]),
            // Add this after your existing stats in the return array
            Stat::make(
                'Last Month\'s Expenses',
                Transaction::where('type', 'debit')
                    ->whereMonth('date', Carbon::now()->subMonth()->month)
                    ->whereYear('date', Carbon::now()->subMonth()->year)
                    ->sum('amount')
            )
                ->description('Total expenses last month')
                ->color('danger')
                ->chart([4, 6, 3, 5, 4, 7, 5])

        ];
    }
}
