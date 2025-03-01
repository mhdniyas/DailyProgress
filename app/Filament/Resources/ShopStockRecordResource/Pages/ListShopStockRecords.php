<?php

namespace App\Filament\Resources\ShopStockRecordResource\Pages;

use App\Filament\Resources\ShopStockRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShopStockRecords extends ListRecords
{
    protected static string $resource = ShopStockRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
