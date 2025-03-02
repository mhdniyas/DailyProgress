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
    public ?string $timeframe = 'month';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $query = Item::query();
        if ($this->filter !== 'all') {
            $query->where('id', $this->filter);
        }
        $items = $query->get();

        $systemStockData = [];
        $shopStockData = [];
        $categories = [];

        foreach ($items as $item) {
            $categories[] = $item->name;

            // Get latest stock records for each item
            $systemStock = StockRecord::where('item_id', $item->id)
                ->latest('recorded_at')
                ->first();

            $shopStock = ShopStockRecord::where('item_id', $item->id)
                ->latest('recorded_at')
                ->first();

            $systemStockData[] = $systemStock ? $systemStock->total_quantity : 0;
            $shopStockData[] = $shopStock ? $shopStock->total_quantity : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'System Stock',
                    'data' => $systemStockData,
                ],
                [
                    'label' => 'Shop Stock',
                    'data' => $shopStockData,
                ],
            ],
            'labels' => $categories,
        ];
    }

    protected function getOptions(): array
    {
        $data = $this->getData();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'System Stock',
                    'data' => $data['datasets'][0]['data'],
                ],
                [
                    'name' => 'Shop Stock',
                    'data' => $data['datasets'][1]['data'],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'endingShape' => 'rounded',
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'xaxis' => [
                'categories' => $data['labels'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Stock Quantity',
                ],
            ],
            'fill' => [
                'opacity' => 1,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) {
                        return val + " units"
                    }',
                ],
            ],
            'colors' => ['#008FFB', '#FF4560'],
        ];
    }

    protected function getDonutOptions(): array
    {
        $data = $this->getData();
        $systemTotal = array_sum($data['datasets'][0]['data']);
        $shopTotal = array_sum($data['datasets'][1]['data']);

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => [$systemTotal, $shopTotal],
            'labels' => ['System Stock', 'Shop Stock'],
            'colors' => ['#008FFB', '#FF4560'],
            'legend' => [
                'position' => 'bottom',
            ],
            'responsive' => [
                [
                    'breakpoint' => 480,
                    'options' => [
                        'chart' => [
                            'width' => 200,
                        ],
                        'legend' => [
                            'position' => 'bottom',
                        ],
                    ],
                ],
            ],
        ];
    }

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

    protected static function getView(): string
    {
        return 'filament.widgets.stock-comparison-charts';
    }
}
