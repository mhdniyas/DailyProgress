<?php

namespace App\Filament\Resources\ShopStockRecordResource\Pages;

use App\Filament\Resources\ShopStockRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShopStockRecord extends CreateRecord
{
    protected static string $resource = ShopStockRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_quantity'] = floatval($data['number_of_bags']) * floatval($data['average_quantity']);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
