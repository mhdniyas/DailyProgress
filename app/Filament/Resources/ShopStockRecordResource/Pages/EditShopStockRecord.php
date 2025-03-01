<?php

namespace App\Filament\Resources\ShopStockRecordResource\Pages;

use App\Filament\Resources\ShopStockRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShopStockRecord extends EditRecord
{
    protected static string $resource = ShopStockRecordResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total_quantity'] = floatval($data['number_of_bags']) * floatval($data['average_quantity']);
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
