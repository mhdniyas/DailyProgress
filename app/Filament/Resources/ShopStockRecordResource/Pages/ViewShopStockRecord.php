<?php

namespace App\Filament\Resources\ShopStockRecordResource\Pages;

use App\Filament\Resources\ShopStockRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShopStockRecord extends ViewRecord
{
    protected static string $resource = ShopStockRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
