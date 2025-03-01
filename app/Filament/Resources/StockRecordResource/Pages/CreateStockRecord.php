<?php

namespace App\Filament\Resources\StockRecordResource\Pages;

use App\Filament\Resources\StockRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockRecord extends CreateRecord
{
    protected static string $resource = StockRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['number_of_bags']) || $data['number_of_bags'] == 0) {
            $data['number_of_bags'] = round(
                floatval($data['total_quantity']) / floatval($data['average_quantity']),
                2
            );
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
