<?php

namespace App\Filament\Resources\DebitResource\Pages;

use App\Filament\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDebit extends CreateRecord
{
    protected static string $resource = DebitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'debit';
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Debit entry created successfully';
    }
}
