<?php

namespace App\Filament\Resources\DebitResource\Pages;

use App\Filament\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDebits extends ListRecords
{
    protected static string $resource = DebitResource::class;
    public function getHeading(): string
    {
        return 'Debit List - ' . auth()->user()->name;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Debit Entry'),
        ];
    }
}
