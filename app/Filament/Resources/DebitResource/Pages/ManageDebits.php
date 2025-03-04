<?php

namespace App\Filament\Resources\DebitResource\Pages;

use App\Filament\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDebits extends ManageRecords
{
    protected static string $resource = DebitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
