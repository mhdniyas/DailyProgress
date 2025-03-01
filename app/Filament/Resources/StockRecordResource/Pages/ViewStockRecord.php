<?php

namespace App\Filament\Resources\StockRecordResource\Pages;

use App\Filament\Resources\StockRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockRecord extends ViewRecord
{
    protected static string $resource = StockRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
