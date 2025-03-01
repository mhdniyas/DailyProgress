<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\StockRecord;
use App\Models\ShopStockRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class StockComparisonTable extends BaseWidget
{
    protected static ?string $heading = 'Stock Comparison Details';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder|Relation|null
    {
        return Item::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Item')
                ->searchable()
                ->sortable(),

            TextColumn::make('system_stock')
                ->label('System Stock')
                ->getStateUsing(function ($record) {
                    $stock = StockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first();
                    return $stock ? $stock->total_quantity : 0;
                })
                ->sortable(),

            TextColumn::make('shop_stock')
                ->label('Shop Stock')
                ->getStateUsing(function ($record) {
                    $stock = ShopStockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first();
                    return $stock ? $stock->total_quantity : 0;
                })
                ->sortable(),

            TextColumn::make('difference')
                ->label('Difference')
                ->getStateUsing(function ($record) {
                    $systemStock = StockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first()?->total_quantity ?? 0;

                    $shopStock = ShopStockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first()?->total_quantity ?? 0;

                    $difference = $systemStock - $shopStock;

                    return sprintf(
                        '%d (%s)',
                        abs($difference),
                        $difference >= 0 ? 'Excess' : 'Shortage'
                    );
                })
                ->color(function ($record) {
                    $systemStock = StockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first()?->total_quantity ?? 0;

                    $shopStock = ShopStockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first()?->total_quantity ?? 0;

                    return ($systemStock - $shopStock) >= 0 ? 'success' : 'danger';
                }),

            TextColumn::make('last_updated')
                ->label('Last Updated')
                ->getStateUsing(function ($record) {
                    $systemRecord = StockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first();

                    $shopRecord = ShopStockRecord::where('item_id', $record->id)
                        ->orderBy('recorded_at', 'desc')
                        ->first();

                    if (!$systemRecord && !$shopRecord) {
                        return 'Never';
                    }

                    $lastUpdate = collect([$systemRecord, $shopRecord])
                        ->filter()
                        ->sortByDesc('recorded_at')
                        ->first();

                    return $lastUpdate ? $lastUpdate->recorded_at->format('Y-m-d H:i:s') : 'Never';
                })
                ->sortable(),
        ];
    }

    public function isTablePaginationEnabled(): bool
    {
        return true;
    }

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 10;
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
