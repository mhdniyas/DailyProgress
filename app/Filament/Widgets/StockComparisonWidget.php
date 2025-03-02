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
                'height' => 350,
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
                'enabled' => true,
                'formatter' => 'function (val) { return val + " units"; }',
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'xaxis' => [
                'categories' => $data['labels'],
                'labels' => [
                    'rotate' => -45,
                    'rotateAlways' => true,
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Stock Quantity',
                ],
            ],
            'grid' => [
                'borderColor' => '#e7e7e7',
                'row' => [
                    'colors' => ['#f3f3f3', 'transparent'],
                    'opacity' => 0.5,
                ],
            ],
            'fill' => [
                'opacity' => 1,
            ],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
                'y' => [
                    'formatter' => 'function (val) { return val + " units"; }',
                ],
            ],
            'colors' => ['#1E3A8A', '#10B981'],
            'title' => [
                'text' => 'Stock Comparison by Item',
                'align' => 'left',
            ],
        ];
    }

    protected function getDonutOptions(): array
    {
        $data = $this->getData();
        $systemTotal = array_sum($data['datasets'][0]['data']);
        $shopTotal = array_sum($data['datasets'][1]['data']);
        $total = $systemTotal + $shopTotal;

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 350,
            ],
            'series' => [$systemTotal, $shopTotal],
            'labels' => ['System Stock', 'Shop Stock'],
            'colors' => ['#1E3A8A', '#10B981'],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => 'function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex] + " units (" + val.toFixed(1) + "%)";
                }',
            ],
            'legend' => [
                'position' => 'bottom',
                'formatter' => 'function(seriesName, opts) {
                    return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + " units";
                }',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => 'Total',
                                'formatter' => 'function (w) { return "' . $total . ' units"; }',
                            ],
                        ],
                    ],
                ],
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
