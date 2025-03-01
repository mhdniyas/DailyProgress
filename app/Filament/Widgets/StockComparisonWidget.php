<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\StockRecord;
use App\Models\ShopStockRecord;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class StockComparisonWidget extends ChartWidget
{
    protected static ?string $heading = 'Stock Comparison';
    
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'all';
    public ?string $timeframe = 'month'; // Default timeframe

    protected function getFilters(): ?array
    {
        $filters = [
            'all' => 'All Items',
        ];

        foreach (Item::all() as $item) {
            $filters[$item->id] = $item->name;
        }

        return $filters;
    }

    protected function getTimeframeFilters(): ?array
    {
        return [
            'week' => 'Week',
            'month' => 'Month',
            'year' => 'Year',
        ];
    }

    protected function getData(): array
    {
        $dates = collect();
        $startDate = Carbon::create(2025, 2, 27);
        
        // Set end date based on timeframe
        switch ($this->timeframe) {
            case 'week':
                $endDate = $startDate->copy()->addDays(7);
                $interval = '1 day';
                $format = 'Y-m-d';
                break;
            case 'month':
                $endDate = $startDate->copy()->addMonth();
                $interval = '1 day';
                $format = 'Y-m-d';
                break;
            case 'year':
                $endDate = $startDate->copy()->addYear();
                $interval = '1 month';
                $format = 'Y-m';
                break;
            default:
                $endDate = $startDate->copy()->addMonth();
                $interval = '1 day';
                $format = 'Y-m-d';
        }

        // Generate dates based on interval
        for ($date = $startDate->copy(); $date->lte($endDate); ) {
            $dates->push($date->format($format));
            if ($interval === '1 day') {
                $date->addDay();
            } else {
                $date->addMonth();
            }
        }

        $query = Item::query();
        if ($this->filter !== 'all') {
            $query->where('id', $this->filter);
        }
        $items = $query->get();

        $datasets = [];
        foreach ($items as $item) {
            // System Stock Dataset
            $systemData = $this->getStockData(StockRecord::class, $item->id, $dates, $startDate, $endDate, $format);
            $datasets[] = [
                'label' => $item->name . ' (System)',
                'data' => $systemData,
                'borderColor' => 'rgb(75, 192, 192)',
                'tension' => 0.1
            ];

            // Shop Stock Dataset
            $shopData = $this->getStockData(ShopStockRecord::class, $item->id, $dates, $startDate, $endDate, $format);
            $datasets[] = [
                'label' => $item->name . ' (Shop)',
                'data' => $shopData,
                'borderColor' => 'rgb(255, 99, 132)',
                'tension' => 0.1
            ];
        }

        return [
            'labels' => $dates->values(),
            'datasets' => $datasets,
        ];
    }

    protected function getStockData($model, $itemId, $dates, $startDate, $endDate, $format)
    {
        $records = $model::where('item_id', $itemId)
            ->whereBetween('recorded_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($format === 'Y-m') {
            // Group by month for yearly view
            $records = $records->groupBy(function ($record) {
                return $record->recorded_at->format('Y-m');
            })->map(function ($group) {
                return $group->last()->total_quantity;
            });
        } else {
            $records = $records->keyBy(fn ($record) => $record->recorded_at->format($format));
        }

        return $dates->map(function ($date) use ($records) {
            return $records[$date]->total_quantity ?? 0;
        })->values();
    }

    protected function getType(): string
    {
        return 'line';
    }
}
