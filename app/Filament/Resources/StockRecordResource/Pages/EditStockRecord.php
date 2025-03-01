<?php

namespace App\Filament\Resources\StockRecordResource\Pages;

use App\Filament\Resources\StockRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockRecord extends EditRecord
{
    protected static string $resource = StockRecordResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!isset($data['number_of_bags']) || $data['number_of_bags'] == 0) {
            $data['number_of_bags'] = round(
                floatval($data['total_quantity']) / floatval($data['average_quantity']),
                2
            );
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
